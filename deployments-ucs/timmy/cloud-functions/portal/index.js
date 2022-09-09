/**
 * Responds to any HTTP request.
 *
 * @param {!express:Request} req HTTP request context.
 * @param {!express:Response} res HTTP response context.
 */

const axios = require('axios');
const querystring = require('querystring');

const {GoogleAuth} = require('google-auth-library');

const {SecretManagerServiceClient} = require('@google-cloud/secret-manager');
const secretManagerClient = new SecretManagerServiceClient();

const {createLogger, format, transports} = require('winston');
const {LoggingWinston} = require('@google-cloud/logging-winston');
const path = require("path");
const loggingWinston = new LoggingWinston();

let logger = null;

const GOOGLE_AUTH_SCOPES = 'https://www.googleapis.com/auth/cloud-platform';
const TENANT_STATUS = {
  PENDING_CREATION: 'pending_creation',
  PENDING_DELETION: 'pending_deletion'
}

/**
 * Initialize the logger
 * @param gcpProjectId the GCP project ID
 * @param logLevel level of severity for logs
 */
function initializeLogger(gcpProjectId, logLevel) {
  logger = createLogger({
    level: logLevel,
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
  if (!process.env[name]) {
    throw new Error(`The environment variable ${name} is missing or undefined`);
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
    if (!data) {
      logger.error(`Failed to retrieved ${secretVersion} ${secretName} secret version from Google Secret Manager. The value is undefined or null`);
      return Promise.reject(new Error(`Failed to retrieved ${secretVersion} ${secretName} secret version from Google Secret Manager. The value is undefined or null`));
    }
    return data.toString('utf-8');

  } catch (err) {
    logger.error(`Failed to retrieve ${secretVersion} ${secretName} secret version from Google Secret Manager. ${err}`);
    return Promise.reject(new Error(`Failed to retrieve ${secretVersion} ${secretName} secret version from Google Secret Manager. ${err}`))
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
  if (!token) {
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

    process.env.GCP_PROJECT_ID = 'akecld-prd-pim-saas-dev';
    process.env.FUNCTION_URL_TIMMY_DELETE_TENANT = ''
    process.env.FUNCTION_URL_TIMMY_CREATE_TENANT = ''
    process.env.LOG_LEVEL = 'info';
    process.env.MAILER_BASE_URL = 'smtp://smtp.mailgun.org:2525';
    process.env.MAILER_DOMAIN = 'mg.cloud.akeneo.com'
    process.env.PORTAL_HOSTNAME = wiremockHostname;
    process.env.PORTAL_LOGIN_HOSTNAME = wiremockHostname;
    process.env.TENANT_EDITION_FLAGS = 'serenity_instance';
    process.env.TENANT_CONTINENT = 'europe';
    process.env.TENANT_ENVIRONMENT = 'sandbox';
    process.env.SECRET_PORTAL = 'TIMMY_PORTAL';
    process.env.SECRET_MAILER_API_KEY = 'MAILER_API_KEY';
  }

  const FUNCTION_URL_TIMMY_CREATE_TENANT = loadEnvironmentVariable('FUNCTION_URL_TIMMY_CREATE_TENANT');
  const FUNCTION_URL_TIMMY_DELETE_TENANT = loadEnvironmentVariable('FUNCTION_URL_TIMMY_DELETE_TENANT');
  const GCP_PROJECT_ID = loadEnvironmentVariable('GCP_PROJECT_ID');
  const LOG_LEVEL = loadEnvironmentVariable('LOG_LEVEL');
  const MAILER_BASE_URL = loadEnvironmentVariable('MAILER_BASE_URL');
  const MAILER_DOMAIN = loadEnvironmentVariable('MAILER_DOMAIN');
  const PORTAL_HOSTNAME = loadEnvironmentVariable('PORTAL_HOSTNAME');
  const PORTAL_LOGIN_HOSTNAME = loadEnvironmentVariable('PORTAL_LOGIN_HOSTNAME');
  const SECRET_PORTAL = loadEnvironmentVariable('SECRET_PORTAL');
  const SECRET_MAILER_API_KEY = loadEnvironmentVariable('SECRET_MAILER_API_KEY');
  const TENANT_EDITION_FLAGS = loadEnvironmentVariable('TENANT_EDITION_FLAGS');
  const TENANT_CONTINENT = loadEnvironmentVariable('TENANT_CONTINENT');
  const TENANT_ENVIRONMENT = loadEnvironmentVariable('TENANT_ENVIRONMENT');

  initializeLogger(GCP_PROJECT_ID, LOG_LEVEL);
  logger.info('Recovery of the tenants from the portal');

  const getTenants = async (status) => {
    const credentials = await getGoogleSecret(GCP_PROJECT_ID, SECRET_PORTAL);
    let url = new URL(`${httpSchema}://${PORTAL_LOGIN_HOSTNAME}/auth/realms/connect/protocol/openid-connect/token`).toString();
    const token = await getToken(url, credentials);

    url = new URL(`${httpSchema}://${PORTAL_HOSTNAME}`)
    return await requestTenantsFromPortal(url, token, status, new URLSearchParams({
      subject_type: TENANT_EDITION_FLAGS,
      continent: TENANT_CONTINENT,
      environment: TENANT_ENVIRONMENT
    }));
  }

  getTenants(TENANT_STATUS.PENDING_CREATION)
    .then((tenants) => {
      getGoogleSecret(GCP_PROJECT_ID, SECRET_MAILER_API_KEY)
        .then((mailerApiKey) => {
          for (const tenant of tenants) {
            const subject = tenant['subject'];
            const cloudInstance = subject['cloud_instance'];
            const instanceName = subject['instance_fqdn']['prefix'];
            const administrator = cloudInstance['administrator'];

            const headers = {
              headers: {
                'Content-Type': 'application/json'
              }
            }
            const payload = {
              instanceName: instanceName,
              mailer: {
                login: `${instanceName}@${MAILER_DOMAIN}`,
                // TODO PH-206: this value must be generated by Timmy and stored in Firestore
                password: Math.random().toString(36).slice(-8),
                base_mailer_url: MAILER_BASE_URL,
                domain: MAILER_DOMAIN,
                api_key: mailerApiKey,
              },
              pim: {
                defaultAdminUser: {
                  login: administrator['email'],
                  firstName: administrator['first_name'],
                  lastName: administrator['last_name'],
                  email: administrator['email'],
                  // TODO PH-206: this value must be generated by Timmy and stored in Firestore
                  password: Math.random().toString(36).slice(-8),
                  uiLocale: cloudInstance['locale']
                },
                monitoring: {
                  // TODO PH-206: this value must be generated by Timmy and stored in Firestore
                  authenticationToken: Math.random().toString(36).slice(-8)
                },
                // TODO PH-206: this value must be generated by Timmy and stored in Firestore
                secret: Math.random().toString(36).slice(-8)
              }
            }
            // When deployed on GCP ask for a token to call the other cloudfunction
            logger.debug(`Ask to Google a token to call the cloudfunction ${FUNCTION_URL_TIMMY_CREATE_TENANT} for the creation of the tenant ${instanceName}`);
            const auth = new GoogleAuth({scopes: GOOGLE_AUTH_SCOPES});
            auth.getAccessToken()
              .then((token) => {
                // TODO : authentication with token is not working. During the workshop we hardcoded one token to go ahead
                headers['headers']['Authorization'] = `Bearer ${token}`;
              })
              .then(() => {
                logger.info(`Prepare following payload to ${FUNCTION_URL_TIMMY_CREATE_TENANT} cloudfunction to create the tenant ${instanceName}: ${JSON.stringify(payload)}`);
                logger.info(`Using Headers: ${JSON.stringify(headers)}`);
                logger.info(`Call the cloudfunction ${FUNCTION_URL_TIMMY_CREATE_TENANT} to create the tenant ${instanceName}`);
                axios.post(FUNCTION_URL_TIMMY_CREATE_TENANT, JSON.stringify(payload), headers)
                  .then(() => {
                    logger.info(`Called the cloudfunction ${FUNCTION_URL_TIMMY_CREATE_TENANT} to create the tenant  ${instanceName}`)
                  })
                  .catch((error) => {
                    logger.error(`Error while calling create tenant: ${error}`);
                  });
              })
              .catch((error) => {
                logger.error(`Error while getting the access token: ${error}`);
              });
          }
        });
    })

    .then(() => {
      logger.info('Dispatched tenant actions to provisioning cloudfunctions');
      res.status(200).send('Dispatched tenant actions to provisioning cloudfunctions');
    })
    .catch((error) => {
      logger.error(`Failed to get tenants to create from the portal: ${error}`);
      res.status(500).send('Failed to call the cloudfunction to create the tenant');
    })
}
