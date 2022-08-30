/**
 * Responds to any HTTP request.
 *
 * @param {!express:Request} req HTTP request context.
 * @param {!express:Response} res HTTP response context.
 */

const axios = require('axios');
const querystring = require('querystring');

const {SecretManagerServiceClient} = require('@google-cloud/secret-manager');
const secretManagerClient = new SecretManagerServiceClient();

const {createLogger, format, transports} = require('winston');
const {LoggingWinston} = require('@google-cloud/logging-winston');
const path = require("path");
const loggingWinston = new LoggingWinston();

let logger = null;

/**
 * Initialize the logger
 * @param gcpProjectId the GCP project ID
 */
function initializeLogger(gcpProjectId) {
  logger = createLogger({
    level: 'info',
    defaultMeta: {
      function: 'ucs-request-portal',
      gcpProjectId: gcpProjectId,
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
      return Promise.reject(new Error(`Failed to retrieved ${secretVersion} ${secretName} secret version from Google Secret Manager. The value is undefined or null`));
    }
    return version.payload.data.toString('utf-8');

  } catch (err) {
    return Promise.reject(new Error(`Failed to retrieve ${secretVersion} ${secretName} secret version from Google Secret Manager`))
  }
}

/**
 * Authenticate and get a token from the portal
 * @param url the authentication portal url
 * @param credentials the credentials needed for authentication
 * @returns {Promise<*>}
 */
async function getToken(url, credentials) {
  logger.info(`Authenticating to the portal ${url} to get a token`);
  const payload = new URLSearchParams(JSON.parse(credentials));
  const headers = {
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      'Content-Length': payload.toString().length,
    }
  }
  const resp = await axios.post(url, payload, headers)
    .catch(function (error) {
      if (error.response) {
        return Promise.reject(new Error(`Authentication failed with status code ${error.response.status}: ${error.response.data.message}`));
      } else if (error.request) {
        return Promise.reject(new Error(`Authentication failed with status code ${error.response.status} due to error in the request: ${error.request}`));
      } else {
        return Promise.reject(new Error(`Authentication failed due to error in the setting up of the request: ${error.message}`));
      }
    });
  const token = await resp['data']['access_token'];
  if (token === undefined || token === null) {
    return Promise.reject(new Error('Failed to token from the portal. It is undefined or null'));
  }

  logger.info(`Successfully authenticated to the portal and got a token`);
  return token;
}

/**
 * Get tenants from the portal
 * @param url the portal base url
 * @param token a token to authenticate to the portal
 * @param status
 * @param filters
 * @returns {Promise<any>}
 */
async function requestTenantsFromPortal(url, token, status, filters) {
  //const apiResourceUrl = new URL(`${status}/${filters}`, url).toString();
  const apiResourceUrl = new URL(`/api/v2/console/requests/${status}?${filters}`, url).toString();
  logger.info(`GET ${apiResourceUrl} - Retrieve tenants from the portal with \`${status}\` status and \`${filters}\` filters`);
  const resp = await axios.get(apiResourceUrl, {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
    }
  }).catch(function (error) {
    const msgPrefix = `Failed to get instances from portal url ${apiResourceUrl}`;
    if (error.response) {
      return Promise.reject(new Error(`${msgPrefix} with status code ${error.response.status}: ${error.response.data.message}`));
    } else if (error.request) {
      return Promise.reject(new Error(`${msgPrefix} with status code ${error.response.status} due to error in the request: ${error.request}`));
    } else {
      return Promise.reject(new Error(`${msgPrefix} due to error in the setting up of the request: ${error.message}`));
    }
  });
  logger.info(`Retrieved tenants from the portal with  \`${status}\` status and \`${filters}\` filters`);

  return await resp.data;
}

exports.requestPortal = (req, res) => {
  let httpSchema = 'https';

  if (process.env.NODE_ENV === 'development') {
    // Wiremock is used through K8S port-forwarding for now.
    const wiremockHostname = 'localhost:8080';
    // Wiremock is not using https for now.
    httpSchema = 'http';

    process.env.SECRET_NAME = 'PORTAL_TIMMY';
    process.env.GCP_PROJECT_ID = 'akecld-prd-pim-saas-dev';
    process.env.PORTAL_HOSTNAME = wiremockHostname;
    process.env.PORTAL_LOGIN_HOSTNAME = wiremockHostname;
    process.env.TENANT_EDITION_FLAGS = 'serenity_instance';
    process.env.TENANT_CONTINENT = 'europe';
    process.env.TENANT_ENVIRONMENT = 'sandbox';
  }

  const SECRET_NAME = loadEnvironmentVariable('SECRET_NAME');
  const GCP_PROJECT_ID = loadEnvironmentVariable('GCP_PROJECT_ID');
  const PORTAL_HOSTNAME = loadEnvironmentVariable('PORTAL_HOSTNAME');
  const PORTAL_LOGIN_HOSTNAME = loadEnvironmentVariable('PORTAL_LOGIN_HOSTNAME');
  const TENANT_EDITION_FLAGS = loadEnvironmentVariable('TENANT_EDITION_FLAGS');
  const TENANT_CONTINENT = loadEnvironmentVariable('TENANT_CONTINENT');
  const TENANT_ENVIRONMENT = loadEnvironmentVariable('TENANT_ENVIRONMENT');

  initializeLogger(GCP_PROJECT_ID);
  logger.info('Recovery of the tenants from the portal');

  const getTenants = async (status) => {
    const credentials = await getGoogleSecret(GCP_PROJECT_ID, SECRET_NAME);
    let url = new URL(`${httpSchema}://${PORTAL_LOGIN_HOSTNAME}/auth/realms/connect/protocol/openid-connect/token`).toString();
    const token = await getToken(url, credentials);

    url = new URL(`${httpSchema}://${PORTAL_HOSTNAME}`)
    return await requestTenantsFromPortal(url, token, status, new URLSearchParams({
        subject_type: TENANT_EDITION_FLAGS,
        continent: TENANT_CONTINENT,
        environment: TENANT_ENVIRONMENT
      })
    );
  }

  getTenants('pending_creation')
    .then((data) => {
      res.status(200).send(data);
    })
    .catch((error) => {
      logger.error(error);
      res.status(500).send(`Failed to get tenants from the portal: ${error}`)
    });
}
