/**
 * Responds to any HTTP request.
 *
 * @param {!express:Request} req HTTP request context.
 * @param {!express:Response} res HTTP response context.
 */

require('dotenv').config();

const crypto = require('crypto');
const axios = require('axios');
const functions = require('@google-cloud/functions-framework');
const {GoogleAuth} = require('google-auth-library');
const auth = new GoogleAuth();
const {createLogger, format, transports} = require('winston');
const {LoggingWinston} = require('@google-cloud/logging-winston');
const path = require('path');
const loggingWinston = new LoggingWinston();

let logger = null;

const NODE_ENV_DEVELOPMENT = 'development';

const DEFAULT_BRANCH_NAME = 'master';
const DEFAULT_PIM_NAMESPACE = 'pim';

const TENANT_STATUS = {
  PENDING_CREATION: 'pending_creation',
  PENDING_DELETION: 'pending_deletion'
}

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

/**
 * Initialize the logger
 */
function initializeLogger(branchName) {
  logger = createLogger({
    level: process.env.LOG_LEVEL,
    defaultMeta: {
      // GCP does not provide id for cloud function instance, we generate it.
      id: crypto.randomUUID(),
      function: process.env.K_SERVICE || 'timmy-request-portal',
      revision: process.env.K_REVISION,
      gcpProjectId: process.env.GCP_PROJECT_ID,
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

function prefixUrlWithBranchName(url, branchName) {
  return (branchName === DEFAULT_BRANCH_NAME ? url : url+'/' + branchName + '/');
}

async function refreshAccessToken(branchName) {
  try {
    const payload = new URLSearchParams(JSON.parse(process.env.TIMMY_PORTAL));
    const instance = axios.create({
      baseURL: prefixUrlWithBranchName(process.env.HTTP_SCHEMA + '://' + process.env.PORTAL_LOGIN_HOSTNAME, branchName),
      timeout: 10000,
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'Content-Length': payload.toString().length,
      }
    });
    const response = await instance.post('auth/realms/connect/protocol/openid-connect/token', payload);
    const token = response.data.access_token;
    if (!token) {
      return Promise.reject('Received access token from the portal is undefined!');
    }
    return Promise.resolve(token);
  } catch (error) {
    const msg = 'Failed to retrieve portal access token: ' + error;
    logger.error(msg);
    return Promise.reject(msg);
  }
}

async function requestTenantsFromPortal(branchName, status, filters) {
  const token = await refreshAccessToken(branchName);
  const instance = axios.create({
    baseURL: prefixUrlWithBranchName(process.env.HTTP_SCHEMA + '://' + process.env.PORTAL_HOSTNAME,  branchName) + '/api/v2/',
    timeout: 10000,
    headers: {
      'Authorization': 'Bearer ' + token,
      'Content-Type': 'application/json'
    }
  });

  instance.interceptors.response.use((response) => {
    return response;
  }, async (error) => {
    const originalRequest = error.config;

    if (error.response.status === 403 && !originalRequest._retry) {
      originalRequest._retry = true;
      const accessToken = await refreshAccessToken(branchName);
      axios.defaults.headers.common['Authorization'] = 'Bearer ' + accessToken;
      return instance(originalRequest);
    }

    return Promise.reject(error);
  });

  const response = await instance.get(`console/requests/${status}?${filters}`);

  //logger.debug('response: ' + response);
  return Promise.resolve(response.data);
}

/**
 * Request a Google cloud function
 * @param url the cloudfunction url
 * @param method the method to call the cloudfunction
 * @param data the payload data to send to the cloudfunction
 * @returns {Promise<unknown>} a
 */

async function requestCloudFunction(url, method, data = null) {
  logger.debug(`Cloud function url: ${url}`);
  logger.debug(`Method: ${url}`);
  logger.debug(`Payload: ${JSON.stringify(data)}`);
  try {
    if (process.env.NODE_ENV !== NODE_ENV_DEVELOPMENT) {
      logger.debug('Request ' + url + ' with target audience ' + url + ' for authentication');
      const client = await auth.getIdTokenClient(url);
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
  requiredEnvironmentVariables([
    'FUNCTION_URL_TIMMY_CREATE_TENANT',
    'FUNCTION_URL_TIMMY_DELETE_TENANT',
    'GCP_PROJECT_ID',
    'HTTP_SCHEMA',
    'LOG_LEVEL',
    'NODE_ENV',
    'PORTAL_HOSTNAME',
    'PORTAL_LOGIN_HOSTNAME',
    'TENANT_CONTINENT',
    'TENANT_EDITION_FLAGS',
    'TENANT_ENVIRONMENT',
    'TIMMY_PORTAL'
  ]);

  // Prefix url with branch name if present
  const branchName = (req.body.branchName || DEFAULT_BRANCH_NAME).toLowerCase();
  const pimNamespace = (branchName === DEFAULT_BRANCH_NAME ? DEFAULT_PIM_NAMESPACE : `pim-${branchName}`);

  initializeLogger(branchName);
  logger.info('Recovery of the tenants from the portal');

  const tenantsToCreate = async () => {
    const tenants = await requestTenantsFromPortal(branchName, TENANT_STATUS.PENDING_CREATION, new URLSearchParams({
      subject_type: process.env.TENANT_EDITION_FLAGS,
      continent: process.env.TENANT_CONTINENT,
      environment: process.env.TENANT_ENVIRONMENT
    }));

    await Promise.allSettled(tenants.map(async tenant => {
      const subject = tenant['subject'];
      const cloudInstance = subject['cloud_instance'];
      const instanceName = subject['instance_fqdn']['prefix'];
      const dnsCloudDomain = subject['instance_fqdn']['suffix'];
      const administrator = cloudInstance['administrator'];

      const payload = {
        branchName: branchName,
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

      logger.info(`Call the cloudfunction ${process.env.FUNCTION_URL_TIMMY_CREATE_TENANT} to create the tenant`);
      try {
        const response = await requestCloudFunction(process.env.FUNCTION_URL_TIMMY_CREATE_TENANT, "POST", JSON.stringify(payload));
        logger.info(`Tenant ${instanceName} is created: ${response.data}`);
      } catch (error) {
        logger.error(`Failed to call the cloudfunction ${process.env.FUNCTION_URL_TIMMY_CREATE_TENANT} to create the tenant: ${JSON.stringify(error)}`);
      }
    }));
  }

  const tenantsToDelete = async () => {
    const tenants = await requestTenantsFromPortal(branchName, TENANT_STATUS.PENDING_DELETION, new URLSearchParams({
      subject_type: process.env.TENANT_EDITION_FLAGS,
      continent: process.env.TENANT_CONTINENT,
      environment: process.env.TENANT_ENVIRONMENT
    }));

    await Promise.allSettled(tenants.map(async tenant => {
      const subject = tenant['subject'];
      const instanceName = subject['instance_fqdn']['prefix'];

      try {
        logger.info(`Call the cloudfunction ${process.env.FUNCTION_URL_TIMMY_DELETE_TENANT} to delete the tenant`);
        const response = await requestCloudFunction(process.env.FUNCTION_URL_TIMMY_DELETE_TENANT, "POST", JSON.stringify({
          instanceName: instanceName,
          branchName: branchName
        }));

        logger.info(`Tenant ${instanceName} is deleted: ${response.data}`);
      } catch (error) {
        logger.error(`Failed to call the cloudfunction ${process.env.FUNCTION_URL_TIMMY_DELETE_TENANT} to delete the tenant: ${JSON.stringify(error)}`);
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
});
