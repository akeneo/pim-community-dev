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

const logger = createLogger({
  level: 'info',
  defaultMeta: {service: 'ucs-tenant-creation'},
  format: format.combine(
    format.timestamp({format: 'YYYY-MM-DD HH:mm:ss'}),
    format.printf(info => {
      return `${info.timestamp} [${info.level}]: ${JSON.stringify(info.message)}`;
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

/**
 * Ensure environment variable is not missing or undefined
 * @param key The name of the environment variable
 * @returns {string} The value of the environment variable
 */
function loadEnvironmentVariable(key) {
  if (typeof process.env[key] === "undefined" || process.env[key] === null) {
    logger.error(`The environment variable ${key} is missing or undefined`);
    throw new Error('Expected environment variable missing');
  }
  return process.env[key]
}

/**
 * Retrieve a token from the ArgoCD server
 * @param url the url of the ArgoCD server
 * @param username the username for authentication
 * @param password the password for authentication
 * @returns {Promise<string|number>} a token
 */
async function getArgoCdToken(url, username, password) {
  try {
    logger.info(`Getting ArgoCD token from ${url} for ${username} user`);
    const resourceUrl = new URL('/api/v1/session', url)

    const payload = JSON.stringify({username: username, password: password});
    const resp = await axios.post(resourceUrl.toString(), payload, {headers: {'Content-Type': 'application/json'}});

    const token = await resp.data.token;
    if (typeof (token) === undefined || token === null) {
      throw new Error('Retrieved token from ArgoCD server is undefined');
    }
    logger.info('Retrieved token from the ArgoCD server');
    return token;
  } catch (err) {
    throw new Error(`Failed to get token from ArgoCD server: ${err}`);
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
  logger.info(`Deleting the ArgoCD application ${appName}`);
  const resourceUrl = new URL(`/api/v1/applications/${appName}`, url).toString();
  const headers = {
    headers: {'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json'}
  };

  await axios.delete(resourceUrl, headers)
    .catch(function (error) {
      if (error.response) {
        logger.error(`ArgoCD app deletion failed with status code ${error.response.status} and following message: ${error.response.data.message}`);
        throw new Error('Failed to delete the ArgoCD app');
      } else if (error.request) {
        logger.error(`ArgoCD app deletion failed with status code ${error.response.status} to error in request: ${error.request}`)
      } else {
        logger.error(`Failed to delete the ArgoCD app due to error in the setting up of the HTTP request: ${error.message}`);
        throw new Error('Failed to delete the ArgoCD app due to error in the setting up of the HTTP request')
      }
    });
  logger.info(`Deleted the ArgoCD application ${appName}`);
}

/**
 * Retrieve a secret value from Google Secret Manager
 * @param gcpProjectId the project id where the secret is
 * @param secretName the name of the secret to retrieve
 * @returns {Promise<string>}
 */
async function getGoogleSecret(gcpProjectId, secretName) {
  try {
    logger.info(`Retrieving secret "${secretName}" from the ${gcpProjectId} GCP project`)
    const [version] = await secretManagerClient.accessSecretVersion({
      name: `projects/${gcpProjectId}/secrets/${secretName}/versions/latest`
    });

    return version.payload.data.toString('utf-8');

  } catch (err) {
    logger.error(`Error when retrieving Google secret ${secretName} from the ${gcpProjectId} GCP project: ${err}`)
    throw new Error(`Failed to retrieved Google Secret ${secretName}: ${err}`);
  }
}

exports.deleteTenant = (req, res) => {
  logger.info('Starting function for tenant deletion')

  if(process.env.NODE_ENV === 'development') {
    process.env.ARGOCD_URL = 'https://argocd2.pim-saas-dev.dev.cloud.akeneo.com';
    process.env.GCP_PROJECT_ID = 'akecld-prd-pim-saas-dev';
  }

  const ARGOCD_URL = loadEnvironmentVariable('ARGOCD_URL');
  const GCP_PROJECT_ID = loadEnvironmentVariable('GCP_PROJECT_ID');

  let instanceName = req.url.split('/')
  instanceName = instanceName[instanceName.length - 1];

  console.log(instanceName)
  const deleteTenant = async () => {
    const ARGOCD_USERNAME = await getGoogleSecret(GCP_PROJECT_ID, 'ARGOCD_USERNAME');
    const ARGOCD_PASSWORD = await getGoogleSecret(GCP_PROJECT_ID, 'ARGOCD_PASSWORD');

    const token = await getArgoCdToken(ARGOCD_URL, ARGOCD_USERNAME, ARGOCD_PASSWORD);
    await deleteArgoCdApp(ARGOCD_URL, token, instanceName);
  };

  deleteTenant()
    .then((resp) => {
      res.status(200).send(`The tenant "${instanceName}" is deleted!`);
    })
    .catch((err) => {
      throw new Error(err);
    });
}
