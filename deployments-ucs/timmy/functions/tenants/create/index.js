/**
 * Responds to any HTTP request.
 *
 * @param {!express:Request} req HTTP request context.
 * @param {!express:Response} res HTTP response context.
 */

'use strict';

const axios = require('axios');
const fs = require('fs');
const yaml = require('js-yaml')
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
    const resp = await axios.post(resourceUrl.toString(), payload, {headers: {'Content-Type': 'application/json'}})

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
 * Template the ArgoCD YAML manifest for the tenant
 * @param params An object containing all the parameters for the template
 * @returns {String} The templated YAML manifest
 */
function templateArgoCdManifest(params) {
  try {
    logger.info('Templating ArgoCD application manifest for the new tenant');
    const template = fs.readFileSync("templates/argocd-application.mustache").toString();
    const rendered = Mustache.render(template, params);
    logger.debug(`The rendered ArgoCD YAML manifest: ${rendered}`);
    logger.info('Templated ArgoCD application manifest for the new tenant');
    return rendered;
  } catch (err) {
    const msg = 'Failed to template the ArgoCD application manifest'
    logger.error(`${msg}: ${err}`);
    throw new Error(msg);
  }
}

/**
 * Cast YAML to JSON format
 * @param content The YAML content to convert into JSON
 * @returns {string} The converted content into JSON
 */
function castYamlToJson(content) {
  try {
    logger.info('Casting templated YAML manifest into JSON');
    const renderedManifestYaml = yaml.load(content);
    const payload = JSON.stringify(renderedManifestYaml, null, 2);
    logger.info('ArgoCD application manifest is casted into JSON');
    logger.debug(`Content of the JSON document: ${payload}`);
    return payload
  } catch (err) {
    const msg = 'Failed to cast the ArgoCD application manifest to JSON'
    logger.error(`${msg}: ${err}`);
    throw new Error(msg);
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
  logger.info('Creating ArgoCD app for the new tenant');
  const resourceUrl = new URL('/api/v1/applications', url)
  const headers = {
    headers: {Authorization: `Bearer ${token}`, 'Content-Type': 'application/json'}
  };
  logger.info('Creating ArgoCD application for the new tenant. Waiting...');

  await axios.post(resourceUrl.toString(), payload, headers)
    .catch(function (error) {
      if (error.response) {
        // The request was made and the server responded with a status code
        // that falls out of the range of 2xx
        logger.error(`ArgoCD app creation failed with status code ${error.response.status} and following message: ${error.response.data.message}`)
        throw new Error('Failed to create the ArgoCD app. HTTP response returned an error');
      } else if (error.request) {
        // The request was made but no response was received
        // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
        // http.ClientRequest in node.js
        logger.error(`ArgoCD app creation failed with status code ${error.response.status} to error in request: ${error.request}`)
        throw new Error('Failed to create the ArgoCD app due to HTTP request');
      } else {
        // Something happened in setting up the request that triggered an Error
        logger.error(`Failed to create the ArgoCD app due to error in the setting up of the HTTP request: ${error.message}`);
        throw new Error('Failed to create the ArgoCD app due to error in the setting up of the HTTP request')
      }
    });
  ;
}

async function getGoogleSecret(gcpProjectId, secretName) {
  try {
    logger.info(`Retrieving secret "${secretName}" from the ${gcpProjectId} GCP project`)
    const [version] = await secretManagerClient.accessSecretVersion({
      name: `projects/${gcpProjectId}/secrets/${secretName}/versions/latest`
    });

    return version.payload.data.toString('utf-8');

  } catch (err) {
    logger.error(`Error when retrieving Google secret ${secretName} from the ${gcpProjectId} GCP project`)
    throw new Error(`Failed to retrieved Google Secret ${secretName}`);
  }
}

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


exports.createTenant = (req, res) => {
  logger.info('Starting function for tenant creation');

  if(process.env.NODE_ENV === 'development') {
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

  // Ensure the json object in the http request body respects the expected schema
  logger.info('Validating the request body schema');
  logger.debug(`HTTP request body: ${req.body}`);
  const schemaCheck = v.validate(req.body, schema);
  if (schemaCheck.valid === false) {
    const msg = schemaCheck.errors[0].message;
    logger.error(`The request body schema is not valid: ${msg}`);
    throw new ValidationError(`Invalid JSON schema for the request body: ${msg}. It must respect this schema: ${JSON.stringify(schema)}`);
  }
  logger.info('Request body data is valid');


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
    mysql: {
      common: {
        persistentDisks: [
          `projects/${GCP_PROJECT_ID}/zones/${GOOGLE_ZONE}/disks/${instanceName}-mysql`
        ]
      }
    },
    pim: {
      storage: {
        bucketName: pfid
      }
    }
  });

  const createTenant = async () => {
    const ARGOCD_USERNAME = await getGoogleSecret(GCP_PROJECT_ID, 'ARGOCD_USERNAME');
    const ARGOCD_PASSWORD = await getGoogleSecret(GCP_PROJECT_ID, 'ARGOCD_PASSWORD');

    const manifest = templateArgoCdManifest(parameters);
    const payload = castYamlToJson(manifest);
    const token = await getArgoCdToken(ARGOCD_URL, ARGOCD_USERNAME, ARGOCD_PASSWORD);
    const resp = await createArgoCdApp(ARGOCD_URL, token, payload);
  }

  createTenant()
    .then((resp) => {
      const msg = `The new tenant "${instanceName}" is successfully created!`
      logger.info(msg);
      res.status(200).send(msg)
    })
    .catch((err) => {
      throw new Error("exception: " + err);
    });
}
