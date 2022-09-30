/**
 * Responds to any HTTP request.
 *
 * @param {!express:Request} req HTTP request context.
 * @param {!express:Response} res HTTP response context.
 */

require('dotenv').config();

const axios = require('axios');
const functions = require('@google-cloud/functions-framework');
const {GoogleAuth} = require('google-auth-library');
const auth = new GoogleAuth();
const {createLogger, format, transports} = require('winston');
const {LoggingWinston} = require('@google-cloud/logging-winston');
const path = require('path');
const loggingWinston = new LoggingWinston();

let logger = null;

const TENANT_STATUS = {
  PENDING_CREATION: 'pending_creation',
  PENDING_DELETION: 'pending_deletion'
}

/**
 * Initialize the logger
 * @param gcpProjectId the GCP project ID
 * @param logLevel level of severity for logs
 * @param branchName
 */
function initializeLogger(gcpProjectId, logLevel, branchName=null) {
  logger = createLogger({
    level: logLevel,
    defaultMeta: {
      function: process.env.K_SERVICE || 'timmy-request-portal',
      revision: process.env.K_REVISION,
      gcpProjectId: gcpProjectId,
      branchName: branchName
    },
    format: format.combine(
      format.timestamp({format: 'YYYY-MM-DD HH:mm:ss'}),
      format.printf(info => {
        return `${info.timestamp} ${info.level}: ${JSON.stringify({
          function: info.function,
          revision: info.revision,
          gcpProjectId: info.gcpProjectId,
          message: info.message,
          branchName: branchName
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
    throw new Error(`Undefined environment variable ${name}`);
  }
  return process.env[name];
}

/**
 * Authenticate and get a token from the portal
 * @param url the authentication portal url
 * @param credentials the credentials needed for authentication
 * @returns {Promise<*>}
 */
async function requestTokenFromPortal(url, credentials) {
  logger.info(`Authenticating to the portal ${url} to get a token`);
  const payload = new URLSearchParams(JSON.parse(credentials));
  const headers = {
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      'Content-Length': payload.toString().length,
    }
  }
  logger.debug(`Payload: ${payload}`);
  logger.debug(`Headers: ${JSON.stringify(headers['headers'])}`);

  try {
    let resp = await axios.post(url, payload, headers);
    const token = await resp['data']['access_token'];
    logger.debug(`Portal token: ${token}`);
    if (!token) {
      return Promise.reject('Received portal token is undefined');
    }
    logger.debug('Successfully authenticated to the portal and got a token');
    return Promise.resolve(token);

  } catch (error) {
    const msg = `Failed to retrieve token from the portal with status code: ${error}`;
    logger.error(msg);
    return Promise.reject(msg);
  }
}

/**
 * Get tenants from the portal
 * @param baseUrl
 * @param token a token to authenticate to the portal
 * @param status
 * @param filters
 * @returns {Promise<any>}
 */
async function requestTenantsFromPortal(baseUrl, token, status, filters) {
  try {
    const headers = {headers: {'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json'}};
    logger.info(`Retrieve tenants from the portal with ${status} status and ${filters} filters`);
    const url = `${baseUrl}/api/v2/console/requests/${status}?${filters}`;
    logger.debug(`GET - ${url}`);
    const resp = await axios.get(url, headers);
    const tenants = resp.data;
    logger.debug(`Tenants with ${status} status and ${filters} filter: + ${JSON.stringify(tenants)}`);
    return Promise.resolve(tenants);
  } catch (error) {
    const msg = `Failed to request tenants with ${status} status from the portal: ${error}`;
    logger.error(msg);
    return Promise.reject(msg);
  }
}

/**
 * Request a Google cloud function
 * @param url the cloudfunction url
 * @param method the method to call the cloudfunction
 * @param data the payload data to send to the cloudfunction
 * @returns {Promise<unknown>} a
 */

async function requestCloudFunction(url, method, data = null) {
  let client = null;
  logger.debug(`Cloud function url: ${url}`);
  logger.debug(`Method: ${url}`);
  logger.debug(`Payload: ${JSON.stringify(data)}`);
  try {
    if (process.env.NODE_ENV !== 'development') {
      logger.debug('Request ' + url + ' with target audience ' + url + ' for authentication');
      const client = await auth.getIdTokenClient(url);
      logger.debug(`Client: ${client}`);
      return client.request({url: url, method: method, data: data});
    } else {
      logger.debug('Use Google default application credentials for authentication');
      return auth.request({url: url, method: method, data: data});
    }
  } catch (error) {
    const msg = `Failed to call the cloud function ${url}: ${error}`;
    logger.error(msg);
    return Promise.reject(msg);
  }
}

functions.http('requestPortal', (req, res) => {
  const FUNCTION_URL_TIMMY_CREATE_TENANT = loadEnvironmentVariable('FUNCTION_URL_TIMMY_CREATE_TENANT');
  const FUNCTION_URL_TIMMY_DELETE_TENANT = loadEnvironmentVariable('FUNCTION_URL_TIMMY_DELETE_TENANT');
  const GCP_PROJECT_ID = loadEnvironmentVariable('GCP_PROJECT_ID');
  const HTTP_SCHEMA = loadEnvironmentVariable('HTTP_SCHEMA');
  const LOG_LEVEL = loadEnvironmentVariable('LOG_LEVEL');
  const NODE_ENV = loadEnvironmentVariable('NODE_ENV');
  const PORTAL_HOSTNAME = loadEnvironmentVariable('PORTAL_HOSTNAME');
  const PORTAL_LOGIN_HOSTNAME = loadEnvironmentVariable('PORTAL_LOGIN_HOSTNAME');
  const TENANT_CONTINENT = loadEnvironmentVariable('TENANT_CONTINENT');
  const TENANT_EDITION_FLAGS = loadEnvironmentVariable('TENANT_EDITION_FLAGS');
  const TENANT_ENVIRONMENT = loadEnvironmentVariable('TENANT_ENVIRONMENT');
  const TIMMY_PORTAL = loadEnvironmentVariable('TIMMY_PORTAL');

  // Prefix url with branch name if present
  const branchName = req.body.branchName || '';
  const pimNamespace = branchName.length ? `pim-${branchName.lowercase}` : 'pim';

  initializeLogger(GCP_PROJECT_ID, LOG_LEVEL, branchName);
  logger.info('Recovery of the tenants from the portal');

  const getTenants = async (status) => {
    let url = new URL(HTTP_SCHEMA + '://' + PORTAL_LOGIN_HOSTNAME + path.join('/', branchName + '/') + 'auth/realms/connect/protocol/openid-connect/token').href.toString();
    const token = await requestTokenFromPortal(url, TIMMY_PORTAL);

    url = new URL(HTTP_SCHEMA + '://' + PORTAL_HOSTNAME + path.join('/', branchName));
    return Promise.resolve(await requestTenantsFromPortal(url, token, status, new URLSearchParams({
      subject_type: TENANT_EDITION_FLAGS,
      continent: TENANT_CONTINENT,
      environment: TENANT_ENVIRONMENT
    })));
  }


  const tenantsToCreate = async () => {
    const tenants = await getTenants(TENANT_STATUS.PENDING_CREATION);
    await Promise.allSettled(tenants.map(async tenant => {
      const subject = tenant['subject'];
      const cloudInstance = subject['cloud_instance'];
      const instanceName = subject['instance_fqdn']['prefix'];
      const dnsCloudDomain = subject['instance_fqdn']['suffix'];
      const administrator = cloudInstance['administrator'];

      const payload = {
        instanceName: instanceName,
        dnsCloudDomain: dnsCloudDomain,
        pim: {
          defaultAdminUser: {
            login: administrator['email'],
            firstName: administrator['first_name'],
            lastName: administrator['last_name'],
            email: administrator['email'],
            uiLocale: cloudInstance['locale']
          },
          api: {
            namespace: pimNamespace
          },
          web: {
            namespace: pimNamespace
          }
        }
      };
      logger.debug("Prepared payload to send for tenant creation: " + JSON.stringify(payload));

      logger.info(`Call the cloudfunction ${FUNCTION_URL_TIMMY_CREATE_TENANT} to create the tenant`);
      try {
        const res = await requestCloudFunction(FUNCTION_URL_TIMMY_CREATE_TENANT, "POST", JSON.stringify(payload));
      } catch (error) {
        logger.error(`Failed to call the cloudfunction ${FUNCTION_URL_TIMMY_CREATE_TENANT} to create the tenant: ${JSON.stringify(error.response.data)}`);
      }
    }));
  }

  const tenantsToDelete = async () => {
    const tenants = await getTenants(TENANT_STATUS.PENDING_DELETION);

    await Promise.allSettled(tenants.map(async tenant => {
      const subject = tenant['subject'];
      const instanceName = subject['instance_fqdn']['prefix'];

      logger.info(`Call the cloudfunction ${FUNCTION_URL_TIMMY_DELETE_TENANT} to delete the tenant`);
      const url = new URL(`/${instanceName}`, FUNCTION_URL_TIMMY_DELETE_TENANT)
      try {
        const resp = await requestCloudFunction(url.toString(), "POST");
      } catch (error) {
        logger.error(`Failed to call the cloudfunction ${url} to delete the tenant: ${JSON.stringify(error)}`);
      }
    }));
  }

  const dispatchActions = async () => {
    logger.info('Dispatch action to provisioning cloud functions');
    await Promise.all([tenantsToCreate(), tenantsToDelete()]);
  }

  dispatchActions(res)
    .then(() => {
      logger.info('Dispatched actions to cloud functions');
      res.status(200).json({
        status_code: 200,
        message: 'Successfully dispatched actions to provisioning cloud functions'
      });
    })
    .catch((error) => {
      logger.error(`Failed to dispatch the tenant actions: ${error}`);
      res.status(500).json({
        status_code: 500,
        message: `Failed to dispatch tenant actions: ${error}`
      });
    });
})
;
