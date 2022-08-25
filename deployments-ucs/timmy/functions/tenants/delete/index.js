/**
 * Responds to any HTTP request.
 *
 * @param {!express:Request} req HTTP request context.
 * @param {!express:Response} res HTTP response context.
 */

'use strict';

const axios = require('axios');
const {createLogger, format, transports} = require('winston');
const {SecretManagerServiceClient} = require('@google-cloud/secret-manager');
const {LoggingWinston} = require('@google-cloud/logging-winston');
const {instance} = require("gaxios");


const loggingWinston = new LoggingWinston();
const secretManagerClient = new SecretManagerServiceClient();

let logger = null;

function initializeLogger(gcpProjectId, instanceName) {
  logger = createLogger({
    level: 'info',
    defaultMeta: {
      function: 'ucs-tenant-creation',
      gcpProjectId: gcpProjectId,
      tenant: instanceName
    },
    format: format.combine(
      format.timestamp({format: 'YYYY-MM-DD HH:mm:ss'}),
      format.printf(info => {
        return `${info.timestamp} ${info.level}: ${JSON.stringify({
          function: info.function,
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
  if (typeof process.env[name] === "undefined" || process.env[name] === null) {
    throw new Error('The environment variable ${name} is missing or undefined');
  }
  return process.env[name];
}

/**
 * Retrieve a token from the ArgoCD server
 * @param url the url of the ArgoCD server
 * @param username the username for authentication
 * @param password the password for authentication
 * @returns {Promise<string|number>} a token
 */
async function getArgoCdToken(url, username, password) {
    logger.info(`Authenticating with ${username} username to ArgoCD server ${url} to get a token`);
    const resourceUrl = new URL('/api/v1/session', url)

    const payload = JSON.stringify({username: username, password: password});
    const resp = await axios.post(resourceUrl.toString(), payload, {headers: {'Content-Type': 'application/json'}})
      .catch(function (error) {
        const msgPrefix = 'Authentication to ArgoCD server failed'
        if (error.response) {
          return Promise.reject(Error(`${msgPrefix} with status code ${error.response.status}: ${error.response.data.message}`));
        } else if (error.request) {
          return Promise.reject(Error(`${msgPrefix} with status code ${error.response.status} due to error in the request : ${error.request}`))
        } else {
          return Promise.reject(Error(`${msgPrefix} due to error in the setting up of the request: ${error.message}`));
        }
      });

    const token = await resp.data.token;
    if (typeof (token) === undefined || token === null) {
      return Promise.reject(Error('Retrieved token from ArgoCD server is undefined'));
    }

    logger.info(`Successfully authenticated with ${username} user to ArgoCD server and got a token`);
    return token;
}

/**
 * Terminate the current running operation on an ArgoCD application
 * @param url the ArgoCD server base url
 * @param token the token to authenticate to the ArgoCD server
 * @param appName the ArgoCD application name
 * @returns {Promise<void>}
 */
async function terminateArgoCdAppOperation(url, token, appName) {
  logger.info(`Asking to ArgoCD server to terminate the currently running operation on the application`);
  const resourceUrl = new URL(`api/v1/applications/${appName}/operation`, url).toString();
  const headers = {
    headers: {'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json'}
  };

  await axios.delete(resourceUrl, headers)
    .catch(function (error) {
      const msgPrefix = 'Termination of the ArgoCD application operation failed'
      if (error.response) {
        return Promise.reject(Error(`${msgPrefix} with status code ${error.response.status}: ${error.response.data.message}`));
      } else if (error.request) {
        return Promise.reject(Error(`${msgPrefix} with status code ${error.response.status} due to error in the request: ${error.request}`));
      } else {
        return Promise.reject(Error(`${msgPrefix} due to error in the setting up of the request: ${error.message}`));
      }
    });

  logger.info(`Terminated successfully the currently running operation on the ArgoCD application`);
}

async function getArgoCdApp(url, token, appName) {
  logger.info(`Retrieving information about the ArgoCD application`);
  const resourceUrl = new URL(`api/v1/applications/${appName}`, url).toString();
  const headers = {
    headers: {'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json'}
  };

  const resp = await axios.get(resourceUrl, headers)
    .catch(function (error) {
      const msgPrefix = 'The recovery of information of the ArgoCD application has failed'
      if (error.response) {
        return Promise.reject(Error(`${msgPrefix} with a status code ${error.response.status}: ${error.response.data.message}`));
      } else if (error.request) {
        return Promise.reject(Error(`${msgPrefix} with status code ${error.response.status} due to error in the request: ${error.request}`));
      } else {
        return Promise.reject(Error(`${msgPrefix} due to error in the setting up of the request: ${error.message}`))
      }
    });

  logger.info(`Got ArgoCD application ${appName} info`)
  return await resp.data;
}


/**
 * Delete an ArgoCD application
 * @param url the ArgoCD url
 * @param token the token to authenticate to ArgoCD
 * @param appName the application name to delete
 * @returns {Promise<void>}
 */
async function deleteArgoCdApp(url, token, appName) {
  logger.info('Asking to ArgoCD server to delete the application');
  const resourceUrl = new URL(`/api/v1/applications/${appName}`, url).toString();
  const headers = {
    headers: {'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json'}
  };

  await axios.delete(resourceUrl, headers)
    .catch(function (error) {
      const msgPrefix = 'The request to remove the application from the ArgoCD server failed';
      if (error.response) {
        return new Promise.reject(Error(`${msgPrefix} with status code ${error.response.status} and following message: ${error.response.data.message}`));
      } else if (error.request) {
        return new Promise.reject(Error(`${msgPrefix} with status code ${error.response.status} to error in request: ${error.request}`))
      } else {
        return new Promise.reject(Error(`${msgPrefix} due to error in the setting up of the HTTP request: ${error.message}`))
      }
    });
  logger.info('The request for deletion of the application has been transmitted to the ArgoCD server');
}

/**
 * Retrieve a secret value from Google Secret Manager
 * @param gcpProjectId the project id where the secret is
 * @param secretName the name of the secret to retrieve
 * @param secretVersion the version of the secret to retrieve
 * @returns {Promise<string>}
 */
async function getGoogleSecret(gcpProjectId, secretName, secretVersion = 'latest') {
  try {
    logger.info(`Retrieving ${secretVersion} ${secretName} secret version from Google Secret Manager`);
    const [version] = await secretManagerClient.accessSecretVersion({
      name: `projects/${gcpProjectId}/secrets/${secretName}/versions/${secretVersion}`
    });

    const data = version.payload.data;
    if (data === 'undefined' || data === null) {
      return Promise.reject(Error(`Failed to retrieved ${secretVersion} ${secretName} secret version from Google Secret Manager. The value is undefined or null`));
    }
    return version.payload.data.toString('utf-8');

  } catch (err) {
    return new Promise.reject(Error(`Failed to retrieve ${secretVersion} ${secretName} secret version from Google Secret Manager`))
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
  const headers = {
    headers: {'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json'}
  };

  const sleep = (milliseconds) => {
    return new Promise(resolve => setTimeout(resolve, milliseconds))
  };

  let currentRetry = 1;
  logger.info('Verify that the deletion of the ArgoCD application is complete');
  let resp = await axios.get(resourceUrl, headers);
  let appNames = resp.data.items.map(x => x.metadata.name)

  while (appNames.includes(appName) && currentRetry <= maxRetries) {
    logger.info(`The ArgoCD application is still being deleted. Next check in ${retryInterval} seconds (${currentRetry}/${maxRetries} retries)`);
    await sleep(retryInterval * 1000);

    resp = await axios.get(resourceUrl, headers);
    appNames = resp.data.items.map(x => x.metadata.name);

    if (!appNames.includes(appName)) {
      logger.info('The ArgoCD application has been deleted');
      return new Promise(resolve => resolve());
    }

    currentRetry++;
  }

  return new Promise.reject(Error('The maximum number of attempts has been exceeded to verify the deletion. Please check the status of the ArgoCD application'));
}

exports.deleteTenant = (req, res) => {
  let instanceName = req.url.split('/')
  instanceName = instanceName[instanceName.length - 1];

  if (typeof (instanceName) === 'undefined' || instanceName === null) {
    res.status(400).send('Tenant name is missing in url: https://function/:tenantName');
  }

  if (process.env.NODE_ENV === 'development') {
    process.env.ARGOCD_URL = 'https://argocd.pim-saas-dev.dev.cloud.akeneo.com';
    process.env.GCP_PROJECT_ID = 'akecld-prd-pim-saas-dev';
  }

  const ARGOCD_URL = loadEnvironmentVariable('ARGOCD_URL');
  const GCP_PROJECT_ID = loadEnvironmentVariable('GCP_PROJECT_ID');

  const deleteTenant = async () => {
    initializeLogger(GCP_PROJECT_ID, instanceName);

    logger.error('Start of the tenant deletion process');
    const ARGOCD_USERNAME = await getGoogleSecret(GCP_PROJECT_ID, 'ARGOCD_USERNAME');
    const ARGOCD_PASSWORD = await getGoogleSecret(GCP_PROJECT_ID, 'ARGOCD_PASSWORD');

    const token = await getArgoCdToken(ARGOCD_URL, ARGOCD_USERNAME, ARGOCD_PASSWORD);
    const app = await getArgoCdApp(ARGOCD_URL, token, instanceName);

    // Operation on ArgoCD app needs to be terminated before deleting the app
    if (app['status']['operationState']['phase'] === 'Running') {
      await terminateArgoCdAppOperation(ARGOCD_URL, token, instanceName);
    }

    await deleteArgoCdApp(ARGOCD_URL, token, instanceName);
    await ensureArgoCdAppIsDeleted(ARGOCD_URL, token, instanceName);
  };

  deleteTenant()
    .then((resp) => {
      res.status(200).send(`The tenant ${instanceName} is deleted!`);
    })
    .catch((error) => {
      logger.error(error);
      res.status(500).send(error);
    });
}
