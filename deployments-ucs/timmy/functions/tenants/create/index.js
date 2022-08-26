/**
 * Responds to any HTTP request.
 *
 * @param {!express:Request} req HTTP request context.
 * @param {!express:Response} res HTTP response context.
 */

'use strict';

const axios = require('axios');
const fs = require('fs');
const yaml = require('js-yaml');
const Mustache = require('mustache');
const merge = require('deepmerge-json');
const {SecretManagerServiceClient} = require('@google-cloud/secret-manager');

const {createLogger, format, transports} = require('winston');
const {LoggingWinston} = require('@google-cloud/logging-winston');
const loggingWinston = new LoggingWinston();

const {Validator, ValidationError} = require("jsonschema");
const v = new Validator();
const schema = require('./schemas/request-body.json');

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
 * Retrieve a token from the ArgoCD server
 * @param url the url of the ArgoCD server
 * @param username the username for authentication
 * @param password the password for authentication
 * @returns {Promise<string|number>} a token
 */
async function getArgoCdToken(url, username, password) {
  logger.info(`Authenticating with ${username} username to ArgoCD server ${url} to get a token`);
  const resourceUrl = new URL('/api/v1/session', url);

  const payload = JSON.stringify({username: username, password: password});
  const resp = await axios.post(resourceUrl.toString(), payload, {headers: {'Content-Type': 'application/json'}})
    .catch(function (error) {
      const msgPrefix = 'Authentication to ArgoCD server failed'
      if (error.response) {
        return Promise.reject(new Error(`${msgPrefix} with status code ${error.response.status}: ${error.response.data.message}`));
      } else if (error.request) {
        return Promise.reject(new Error(`${msgPrefix} with status code ${error.response.status} due to error in the request : ${error.request}`))
      } else {
        return Promise.reject(new Error(`${msgPrefix} due to error in the setting up of the request: ${error.message}`));
      }
    });

  const token = await resp.data.token;
  if (typeof (token) === undefined || token === null) {
    return Promise.reject(new Error('Retrieved token from ArgoCD server is undefined'));
  }

  logger.info(`Successfully authenticated with ${username} user to ArgoCD server and got a token`);
  return token;
}

/**
 * Template the ArgoCD YAML manifest for the tenant
 * @param params An object containing all the parameters for the template
 * @returns {String} The templated YAML manifest
 */
function templateArgoCdManifest(params) {
  try {
    logger.info('Template of the manifest of the ArgoCD application for the new tenant');

    const template = fs.readFileSync("templates/argocd-application.mustache").toString();
    const rendered = Mustache.render(template, params);

    logger.debug(`Rendered ArgoCD YAML manifest: ${rendered}`);
    logger.info('The ArgoCD application manifest has been templated for the new tenant');
    return rendered;
  } catch (error) {
    throw new Error(`The templating of the ArgoCD application manifest failed: ${error}`);
  }
}

/**
 * Cast YAML to JSON format
 * @param content The YAML content to convert into JSON
 * @returns {string} The converted content into JSON
 */
function castYamlToJson(content) {
  try {
    logger.info('Cast the ArgoCD application manifest as a json document');
    const renderedManifestYaml = yaml.load(content);
    const payload = JSON.stringify(renderedManifestYaml, null, 2);
    logger.info('The ArgoCD application manifest has been converted to a JSON document');
    logger.debug(`The ArgoCD JSON document: ${payload}`);
    return payload;
  } catch (err) {
    throw new Error(`Failed to cast the ArgoCD application manifest to JSON: ${err}`);
  }
}

/**
 * Create an ArgoCD application through the REST API
 * @param url The ArgoCD server url
 * @param token A token to authenticate to ArgoCD server REST API
 * @param payload The JSON payload containing the application definition
 * @returns {Promise<*>}
 */
async function createArgoCdApp(url, token, payload) {
  logger.info('Creating of the ArgoCD application for the new tenant');
  const resourceUrl = new URL('/api/v1/applications', url);
  const headers = {
    headers: {Authorization: `Bearer ${token}`, 'Content-Type': 'application/json'}
  };

  await axios.post(resourceUrl.toString(), payload, headers)
    .catch(function (error) {
      const msgPrefix = 'The creation of the ArgoCD application for the tenant has failed';
      if (error.response) {
        // The request was made and the server responded with a status code
        // that falls out of the range of 2xx
        return Promise.reject(new Error(`${msgPrefix} with status code ${error.response.status} and following message: ${error.response.data.message}`))
      } else if (error.request) {
        // The request was made but no response was received
        // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
        // http.ClientRequest in node.js
        return Promise.reject(new Error(`${msgPrefix} with status code ${error.response.status} to error in request: ${error.request}`))
      } else {
        // Something happened in setting up the request that triggered an Error
        return Promise.reject(new Error(`${msgPrefix} due to error in the setting up of the HTTP request: ${error.message}`))
      }
    });
}

/**
 *
 * @param url
 * @param token
 * @param appName
 * @param maxRetries
 * @param retryInterval
 * @returns {Promise<unknown>}
 */
async function ensureArgoCdAppIsHealthy(url, token, appName, maxRetries = 60, retryInterval = 10) {
  const resourceUrl = new URL(`/api/v1/applications/${appName}`, url).toString();
  const headers = {
    headers: {'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json'}
  };

  const sleep = (milliseconds) => {
    return new Promise(resolve => setTimeout(resolve, milliseconds))
  };

  const HEALTHY_STATUS = 'Healthy';
  const DEGRADED_STATUS = 'Degraded';

  let currentRetry = 1;

  logger.info('Verify that the the ArgoCD application is healthy');
  let resp = await axios.get(resourceUrl, headers);

  let healthStatus = resp.data.status.health.status;

  while (healthStatus !== HEALTHY_STATUS && currentRetry <= maxRetries) {
    logger.info(`The ArgoCD application is being created and not healthy yet (health status: ${healthStatus}). Next check in ${retryInterval} seconds (${currentRetry}/${maxRetries} retries)`);
    await sleep(retryInterval * 1000);

    resp = await axios.get(resourceUrl, headers);
    healthStatus = resp.data.status.health.status;

    if (healthStatus === HEALTHY_STATUS) {
      logger.info('The ArgoCD application is created and healthy');
      return new Promise(resolve => resolve);
    }

    if (healthStatus === DEGRADED_STATUS) {
      const msg = resp.data.status.operationState.message;
      return Promise.reject(new Error(`The ArgoCD application health is degraded: ${msg}`));
    }

    currentRetry++;
  }

  return Promise.reject(new Error('The maximum number of attempts has been exceeded to verify the deletion. Please check the status of the ArgoCD application'));
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


exports.createTenant = (req, res) => {

  if (process.env.NODE_ENV === 'development') {
    process.env.ARGOCD_URL = 'https://argocd.pim-saas-dev.dev.cloud.akeneo.com/';
    process.env.GOOGLE_ZONE = 'europe-west1-b';
    process.env.GCP_PROJECT_ID = 'akecld-prd-pim-saas-dev';
    process.env.GOOGLE_MANAGED_ZONE_DNS = 'dev.akeneo.ch'
  }

  // For local development
  const ARGOCD_URL = new URL(loadEnvironmentVariable("ARGOCD_URL")).toString();
  const GOOGLE_ZONE = loadEnvironmentVariable("GOOGLE_ZONE");
  const GCP_PROJECT_ID = loadEnvironmentVariable("GCP_PROJECT_ID");
  const GOOGLE_MANAGED_ZONE_DNS = loadEnvironmentVariable("GOOGLE_MANAGED_ZONE_DNS");

  initializeLogger(GCP_PROJECT_ID, req.body.instanceName);

  // Ensure the json object in the http request body respects the expected schema
  logger.info('Validation of the JSON schema of the request body');
  logger.debug(`HTTP request JSON body: ${req.body}`);
  const schemaCheck = v.validate(req.body, schema);
  if (schemaCheck.valid === false) {
    const msg = schemaCheck.errors[0].message;
    logger.error(`The JSON schema of the received http body is not valid: ${msg}`);
    res.status(400).send('Bad JSON schema in the request http body');
  }
  logger.info('The JSON schema of the http body of the request is valid');


  const instanceName = req.body.instanceName;
  const pimMasterDomain = `${instanceName}.${GOOGLE_MANAGED_ZONE_DNS}`;
  const extraLabelType = 'ucs'
  const pfid = `${extraLabelType}-${instanceName}`;

  // Deep merge of the request json body and the computed json object
  const parameters = merge(req.body, {
    backup: {
      enabled: false
    },
    common: {
      gcpProjectID: GCP_PROJECT_ID,
      googleZone: GOOGLE_ZONE,
      pimMasterDomain: pimMasterDomain,
      dnsCloudDomain: 'dev.akeneo.ch',
      workloadIdentityGSA: 'main-service-account',
      workloadIdentityKSA: `${pfid}-ksa-workload-identity`,
    },
    global: {
      extraLabels: {
        instanceName: instanceName,
        pfid: pfid,
        instance_dns_zone: GOOGLE_MANAGED_ZONE_DNS,
        instance_dns_record: pimMasterDomain,
        papo_project_code: instanceName,
        papo_project_code_truncated: instanceName,
        papo_project_code_hashed: instanceName,
        type: extraLabelType,
      }
    },
    image: {
      pim: {        // TODO : temporary value to test need to be variabilized
        repository: 'europe-west1-docker.pkg.dev/akecld-prd-pim-saas-dev/pim-enterprise-dev/pim-enterprise-dev',
        tag: 'v20220822000000'
      }
    },
    mysql: {
      common: {
        persistentDisks: [
          `projects/${GCP_PROJECT_ID}/zones/${GOOGLE_ZONE}/disks/${pfid}-mysql`
        ]
      }
    },
    pim: {
      storage: {
        bucketName: pfid
      },
    }
  });

  const createTenant = async () => {
    const ARGOCD_USERNAME = await getGoogleSecret(GCP_PROJECT_ID, 'ARGOCD_USERNAME');
    const ARGOCD_PASSWORD = await getGoogleSecret(GCP_PROJECT_ID, 'ARGOCD_PASSWORD');

    const manifest = templateArgoCdManifest(parameters);
    const payload = castYamlToJson(manifest);
    const token = await getArgoCdToken(ARGOCD_URL, ARGOCD_USERNAME, ARGOCD_PASSWORD);
    const resp = await createArgoCdApp(ARGOCD_URL, token, payload);
    await ensureArgoCdAppIsHealthy(ARGOCD_URL, token, instanceName);
  }

  createTenant()
    .then((resp) => {
      const msg = `The new tenant ${instanceName} is successfully created!`
      logger.info(msg);
      res.status(200).send(msg);
    })
    .catch((error) => {
      logger.error(error);
      res.status(500).send(error);
    });
}
