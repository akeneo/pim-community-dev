/**
 * Responds to any HTTP request.
 *
 * @param {!express:Request} req HTTP request context.
 * @param {!express:Response} res HTTP response context.
 */

'use strict';

require('dotenv').config();

const crypto = require('crypto');
const axios = require('axios');
const {createLogger, format, transports} = require('winston');
const functions = require('@google-cloud/functions-framework');
const {LoggingWinston} = require('@google-cloud/logging-winston');
const loggingWinston = new LoggingWinston();
const {Validator, ValidationError} = require("jsonschema");
const v = new Validator();
const schema = require('./schemas/request-body.json');
const {Firestore} = require('@google-cloud/firestore');
const https = require("https");

let firestoreCollection = null;
let logger = null;

const httpsAgent = new https.Agent({
  // Trust self-signed certificates
  rejectUnauthorized: false
});

const DEFAULT_BRANCH_NAME = 'master';
const FIRESTORE_STATUS = {
  DELETION_IN_PREPARATION: "deletion_in_preparation",
  DELETION_IN_PROGRESS: "deletion_in_progress"
};

/**
 * Ensure the presence of the required environment variables
 * @param names list of required environment variables
 */
function requiredEnvironmentVariables(names) {
  let envArr = {};
  const missingVariables = [];

  names.forEach(name => {
    !process.env[name] && missingVariables.push(name);
    envArr[name] = process.env[name];
  });

  if (missingVariables.length) {
    throw new Error('Environment variables needed: ' + JSON.stringify(missingVariables));
  }
}

function initializeLogger(branchName, instanceName) {
  logger = createLogger({
    level: process.env.LOG_LEVEL,
    defaultMeta: {
      id: crypto.randomUUID(),
      function: process.env.K_SERVICE || 'timmy-delete-tenant',
      revision: process.env.K_REVISION,
      gcpProjectId: process.env.GCP_PROJECT_ID,
      gcpProjectFirestoreId: process.env.GCP_FIRESTORE_PROJECT_ID,
      tenant: instanceName,
      branchName: branchName
    },
    format: format.combine(
      format.timestamp({format: 'YYYY-MM-DD HH:mm:ss'}),
      format.printf(info => {
        return `${info.timestamp} ${info.level}: ${JSON.stringify({
          id: info.id,
          function: info.function,
          revision: info.revision,
          gcpProjectId: info.gcpProjectId,
          gcpProjectFirestoreId: info.gcpProjectFirestoreId,
          message: info.message,
          tenant: info.tenant,
          branchName: info.branchName
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
 * @returns {Promise<string|number>} a token
 */
async function getArgoCdToken() {
  logger.info(`Authenticate with ${process.env.ARGOCD_USERNAME} username to ArgoCD server ${process.env.ARGOCD_URL} to get a token`);
  const url = new URL('/api/v1/session', process.env.ARGOCD_URL);
  const config = {httpsAgent: httpsAgent, headers: {'Content-Type': 'application/json'}};
  const payload = JSON.stringify({username: process.env.ARGOCD_USERNAME, password: process.env.ARGOCD_PASSWORD});

  try {
    const resp = await axios.post(url.href.toString(), payload, config);
    const token = resp.data.token
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
 * @param token the token to authenticate to the ArgoCD server
 * @param appName the ArgoCD application name
 * @returns {Promise<void>}
 */
async function terminateArgoCdAppOperation(token, appName) {
  logger.info(`Ask ArgoCD server terminating the currently running operation on the application`);
  const url = new URL(`api/v1/applications/${appName}/operation`, process.env.ARGOCD_URL);
  const config = {
    httpsAgent: httpsAgent,
    headers: {'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json'}
  };

  try {
    return Promise.resolve(await axios.delete(url.href.toString(), config));
  } catch (error) {
    const msg = formatAxiosError('Failed to ask ArgoCD to terminate operation on the application', error);
    logger.error(msg);
    return Promise.reject(msg);
  }
}

/**
 * Retrieve information of an ArgoCD application
 * @param token the token to authenticate to the ArgoCD server
 * @param appName the ArgoCD application name
 * @returns {Promise<unknown>}
 */
async function getArgoCdApp(token, appName) {
  logger.info(`Retrieving information about the ArgoCD application`);
  const url = new URL(`api/v1/applications/${appName}`, process.env.ARGOCD_URL);
  const config = {
    httpsAgent: httpsAgent,
    headers: {'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json'}
  };

  try {
    const resp = await axios.get(url.href.toString(), config);
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
async function deleteArgoCdApp(token, appName) {
  logger.info('Ask ArgoCD server to delete the application');
  const url = new URL(`/api/v1/applications/${appName}`, process.env.ARGOCD_URL);
  const config = {
    httpsAgent: httpsAgent,
    headers: {'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json'}
  };

  try {
    return Promise.resolve(await axios.delete(url.href.toString(), config));
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
async function ensureArgoCdAppIsDeleted(token, appName, maxRetries = 30, retryInterval = 10) {
  const url = new URL(`/api/v1/applications`, process.env.ARGOCD_URL);
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
    resp = await axios.get(url.href.toString(), config);
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
      resp = await axios.get(url.href.toString(), config);
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

async function updateFirestoreDocStatus(firestore, doc, status) {
  try {
    logger.info(`Update the ${doc} firestore document in ${firestoreCollection} collection with ${status} status`);
    return Promise.resolve(await firestore.collection(firestoreCollection).doc(doc).set({
        status: status,
        status_date: new Date().toISOString()
    }, {merge: true}));

  } catch (error) {
    const msg = `Failed to update the ${doc} firestore document in ${firestoreCollection} collection: ${error}`;
    logger.error(msg);
    return Promise.reject(msg);
  }
}

async function deleteFirestoreDocument(firestore, doc) {
  try {
    logger.info(`Delete the ${doc} firestore document in ${firestoreCollection} collection`);
    await firestore.collection(firestoreCollection).doc(doc).delete();
  } catch (error) {
    const msg = `Failed to delete the ${doc} firestore document in ${firestoreCollection} collection: ${error}`
    logger.error(msg);
    return Promise.reject(msg);
  }
}

functions.http('deleteTenant', (req, res) => {
  requiredEnvironmentVariables([
    'ARGOCD_PASSWORD',
    'ARGOCD_URL',
    'ARGOCD_USERNAME',
    'GCP_FIRESTORE_PROJECT_ID',
    'GCP_PROJECT_ID',
    'LOG_LEVEL',
    'TENANT_CONTEXT_COLLECTION_NAME',
  ]);

  const body = JSON.parse(req.body);
  const branchName = body.branchName;
  const instanceName = body.instanceName;

  initializeLogger(branchName, instanceName);

  logger.info('Validation of the JSON schema of the request body');
  logger.debug(`HTTP request JSON body: ${JSON.stringify(req.body)}`);

  const schemaCheck = v.validate(body, schema);
  if (!schemaCheck.valid) {
    const error = schemaCheck.errors[0].message;
    res.status(400).json({
      status_code: 400,
      message: `HTTP body json is not valid: ${error}`,
    });
  }

  firestoreCollection = `${process.env.REGION}/${branchName}/${process.env.TENANT_CONTEXT_COLLECTION_NAME}`;

  logger.debug('Instantiate firestore instance');
  const firestore = new Firestore({
    projectId: process.env.GCP_FIRESTORE_PROJECT_ID,
    timestampsInSnapshots: true
  });

  const deleteTenant = async () => {
    await updateFirestoreDocStatus(firestore, instanceName, FIRESTORE_STATUS.DELETION_IN_PREPARATION);
    const token = await getArgoCdToken();
    const app = await getArgoCdApp(token, instanceName)

    // Operation on ArgoCD app needs to be terminated before deleting the app
    if (app['status']['operationState']['phase'] === 'Running') {
      await terminateArgoCdAppOperation(token, instanceName);
    }

    await deleteArgoCdApp(token, instanceName);
    await updateFirestoreDocStatus(firestore, instanceName, FIRESTORE_STATUS.DELETION_IN_PROGRESS);
    await ensureArgoCdAppIsDeleted(token, instanceName);
  }


  deleteTenant(res)
    .then(async () => {
      await deleteFirestoreDocument(firestore, instanceName);
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
