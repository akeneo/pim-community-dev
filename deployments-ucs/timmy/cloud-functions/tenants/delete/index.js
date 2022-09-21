/**
 * Responds to any HTTP request.
 *
 * @param {!express:Request} req HTTP request context.
 * @param {!express:Response} res HTTP response context.
 */

'use strict';

require('dotenv').config();

const axios = require('axios');
const {createLogger, format, transports} = require('winston');
const functions = require('@google-cloud/functions-framework');
const {LoggingWinston} = require('@google-cloud/logging-winston');
const loggingWinston = new LoggingWinston();
const {Firestore} = require('@google-cloud/firestore');
const https = require("https");

let logger = null;

const httpsAgent = new https.Agent({
  // Trust self-signed certificates
  rejectUnauthorized: false
});

const FIRESTORE_STATUS = {
  DELETION_IN_PREPARATION: "deletion_in_preparation",
  DELETION_IN_PROGRESS: "deletion_in_progress"
};

function initializeLogger(gcpProjectId, logLevel, instanceName) {
  logger = createLogger({
    level: logLevel,
    defaultMeta: {
      function: process.env.K_SERVICE || 'timmy-delete-tenant',
      revision: process.env.K_REVISION,
      gcpProjectId: gcpProjectId,
      tenant: instanceName
    },
    format: format.combine(
      format.timestamp({format: 'YYYY-MM-DD HH:mm:ss'}),
      format.printf(info => {
        return `${info.timestamp} ${info.level}: ${JSON.stringify({
          function: info.function,
          revision: info.revision,
          gcpProjectId: info.gcpProjectId,
          message: info.message,
          tenant: info.tenant
        })}`;
      }),
    ),
    transports: [
      new transports.Console({
        handleExceptions: true,
        handleRejections: true
      }),
      loggingWinston,
    ],
    exitOnError: false,
  });
}

/**
 * Ensure environment variable is not missing or undefined
 * @param name The name of the environment variable
 * @returns {string} The value of the environment variable
 */
function loadEnvironmentVariable(name) {
  if (!process.env[name]) {
    throw new Error(`The environment variable ${name} is missing or undefined`);
  }
  return process.env[name];
}

function formatAxiosError(msg, error) {
  if (error.response) {
    msg += ' with ' + error.response.status + ' status code: ' + JSON.stringify(error.response.data);
  } else if (error.request) {
    msg += ' because no response was received: ' + JSON.stringify(error.request);
  } else {
    msg += ': ' + error.message;
  }
  return msg
}

/**
 * Retrieve a token from the ArgoCD server
 * @param url the url of the ArgoCD server
 * @param username the username for authentication
 * @param password the password for authentication
 * @returns {Promise<string|number>} a token
 */
async function getArgoCdToken(url, username, password) {
  logger.info(`Authenticate with ${username} username to ArgoCD server ${url} to get a token`);
  const resourceUrl = new URL('/api/v1/session', url);
  const config = {httpsAgent: httpsAgent, headers: {'Content-Type': 'application/json'}};
  const payload = JSON.stringify({username: username, password: password});

  try {
    const resp = await axios.post(resourceUrl.toString(), payload, config);
    const token = await resp.data.token
    if (!token) {
      const msg = 'The ArgoCD token is undefined';
      logger.error(msg);
      return Promise.reject(msg);
    }
    return Promise.resolve(token);
  } catch (error) {
    const msg = formatAxiosError('Failed to retrive token from ArgoCD', error);
    logger.error(msg);
    return Promise.reject(msg);
  }
}

/**
 * Terminate the current running operation on an ArgoCD application
 * @param url the ArgoCD server base url
 * @param token the token to authenticate to the ArgoCD server
 * @param appName the ArgoCD application name
 * @returns {Promise<void>}
 */
async function terminateArgoCdAppOperation(url, token, appName) {
  logger.info(`Ask ArgoCD server terminating the currently running operation on the application`);
  const path = `api/v1/applications/${appName}/operation`;
  const resourceUrl = new URL(path, url).toString();
  const config = {
    httpsAgent: httpsAgent,
    headers: {'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json'}
  };

  try {
    return Promise.resolve(await axios.delete(resourceUrl, config));
  } catch (error) {
    const msg = formatAxiosError('Failed to ask ArgoCD to terminate operation on the application', error);
    logger.error(msg);
    return Promise.reject(msg);
  }
}

/**
 * Retrieve information of an ArgoCD application
 * @param url the ArgoCD server url
 * @param token the token to authenticate to the ArgoCD server
 * @param appName the ArgoCD application name
 * @returns {Promise<unknown>}
 */
async function getArgoCdApp(url, token, appName) {
  logger.info(`Retrieving information about the ArgoCD application`);
  const path = `api/v1/applications/${appName}`;
  const resourceUrl = new URL(path, url).toString();
  const config = {
    httpsAgent: httpsAgent,
    headers: {'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json'}
  };

  try {
    const resp = await axios.get(resourceUrl, config);
    return Promise.resolve(resp.data);
  } catch (error) {
    const msg = formatAxiosError('Failed to get ArgoCD application info', error);
    logger.error(msg);
    return Promise.reject(msg);
  }
}

/**
 * Delete an ArgoCD application
 * @param url the ArgoCD url
 * @param token the token to authenticate to ArgoCD
 * @param appName the application name to delete
 * @returns {Promise<void>}
 */
async function deleteArgoCdApp(url, token, appName) {
  logger.info('Ask ArgoCD server to delete the application');
  const path = `/api/v1/applications/${appName}`
  const resourceUrl = new URL(path, url).toString();
  const config = {
    httpsAgent: httpsAgent,
    headers: {'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json'}
  };

  try {
    return Promise.resolve(await axios.delete(resourceUrl, config));
  } catch (error) {
    const msg = formatAxiosError('Failed to ask ArgoCD to delete the application', error);
    logger.error(msg);
    return Promise.reject(msg);
  }
}

/**
 * Control if the ArgoCD application is deleted
 * @param url the ArgoCD server base url
 * @param token a token to authenticate to the ArgoCD server
 * @param appName the name of the application
 * @param maxRetries the maximum number of retries
 * @param retryInterval the interval in seconds between each retry
 * @returns {Promise<void>}
 */
async function ensureArgoCdAppIsDeleted(url, token, appName, maxRetries = 30, retryInterval = 10) {
  const resourceUrl = new URL(`/api/v1/applications`, url).toString();
  const config = {
    httpsAgent: httpsAgent,
    headers: {'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json'}
  };

  const sleep = (milliseconds) => {
    return new Promise(resolve => setTimeout(resolve, milliseconds))
  };

  let currentRetry = 1;
  let resp;
  let appNames;
  let msg = 'Failed to follow ArgoCD application deletion progression';

  try {
    logger.info('Verify that the deletion of the ArgoCD application is complete');
    resp = await axios.get(resourceUrl, config);
    appNames = resp.data.items.map(x => x.metadata.name)
  } catch (error) {
    const msg = formatAxiosError(msg, error);
    logger.error(msg);
    return Promise.reject(msg);
  }

  while (appNames.includes(appName) && currentRetry <= maxRetries) {
    try {
      logger.info(`The ArgoCD application is still being deleted. Next check in ${retryInterval} seconds (${currentRetry}/${maxRetries} retries)`);
      await sleep(retryInterval * 1000);
      resp = await axios.get(resourceUrl, config);
      appNames = resp.data.items.map(x => x.metadata.name);

      if (!appNames.includes(appName)) {
        logger.info('The ArgoCD application is deleted');
        return Promise.resolve();
      }

      currentRetry++;
    } catch (error) {
      const msg = formatAxiosError(msg, error);
      logger.error(msg);
      return Promise.reject(msg);
    }
  }

  msg = `The maximum number of attempts has been exceeded to verify the deletion. Please check the status of the ArgoCD application at ${url}/applications/${appName}`;
  logger.error(msg);
  return Promise.reject(msg);
}

async function updateFirestoreDocStatus(firestore, collection, doc, status) {
  try {
    logger.info(`Update the ${doc} firestore document in ${collection} collection with ${status} status`);
    return Promise.resolve(await firestore.collection(collection).doc(doc).set({
        status: status,
        status_date: new Date().toISOString()
    }, {merge: true}));

  } catch (error) {
    const msg = `Failed to update the ${doc} firestore document in ${collection} collection: ${error}`;
    logger.error(msg);
    return Promise.reject(msg);
  }
}

async function deleteFirestoreDocument(firestore, collection, doc) {
  try {
    logger.info(`Delete the ${doc} firestore document in ${collection} collection`);
    await firestore.collection(collection).doc(doc).delete();
  } catch (error) {
    const msg = `Failed to delete the ${doc} firestore document in ${collection} collection: ${error}`
    logger.error(msg);
    return Promise.reject(msg);
  }
}

functions.http('deleteTenant', (req, res) => {
  let instanceName = req.url.split('/');
  instanceName = instanceName[instanceName.length - 1];

  if (!instanceName) {
    throw new Error('The tenant name is missing in the url: https://function_url/:tenantName')
  }

  const ARGOCD_USERNAME = loadEnvironmentVariable('ARGOCD_USERNAME');
  const ARGOCD_PASSWORD = loadEnvironmentVariable('ARGOCD_PASSWORD');
  const ARGOCD_URL = loadEnvironmentVariable('ARGOCD_URL');
  const GCP_PROJECT_ID = loadEnvironmentVariable('GCP_PROJECT_ID');
  const LOG_LEVEL = loadEnvironmentVariable('LOG_LEVEL');
  const TENANT_CONTEXT = loadEnvironmentVariable('TENANT_CONTEXT');
  const GCP_FIRESTORE_PROJECT_ID = loadEnvironmentVariable('GCP_FIRESTORE_PROJECT_ID');

  initializeLogger(GCP_PROJECT_ID, LOG_LEVEL, instanceName);

  logger.debug('Instantiate firestore instance');
  const firestore = new Firestore({
    projectId: GCP_FIRESTORE_PROJECT_ID,
    timestampsInSnapshots: true
  });


  const deleteTenant = async () => {
    await updateFirestoreDocStatus(firestore, TENANT_CONTEXT, instanceName, FIRESTORE_STATUS.DELETION_IN_PREPARATION);
    const token = await getArgoCdToken(ARGOCD_URL, ARGOCD_USERNAME, ARGOCD_PASSWORD);
    const app = await getArgoCdApp(ARGOCD_URL, token, instanceName)

    // Operation on ArgoCD app needs to be terminated before deleting the app
    if (app['status']['operationState']['phase'] === 'Running') {
      await terminateArgoCdAppOperation(ARGOCD_URL, token, instanceName);
    }

    await deleteArgoCdApp(ARGOCD_URL, token, instanceName);
    await updateFirestoreDocStatus(firestore, TENANT_CONTEXT, instanceName, FIRESTORE_STATUS.DELETION_IN_PROGRESS);
    await ensureArgoCdAppIsDeleted(ARGOCD_URL, token, instanceName);
  }


  deleteTenant(res)
    .then(async () => {
      await deleteFirestoreDocument(firestore, TENANT_CONTEXT, instanceName);
      logger.info('Successfully deleted the tenant');
      res.status(200).json({
        status_code: 200,
        message: `Successfully deleted the tenant ${instanceName}`
      })
    })
    .catch((error) => {
      logger.error(`Failed to delete the tenant: ${error}`);
      res.status(500).json({
        status_code: 500,
        message: `Failed to delete the tenant ${instanceName}: ${error}`
      })
    });
});
