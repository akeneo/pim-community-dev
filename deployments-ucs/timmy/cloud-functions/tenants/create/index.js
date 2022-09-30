/**
 * Responds to any HTTP request.
 *
 * @param {!express:Request} req HTTP request context.
 * @param {!express:Response} res HTTP response context.
 */

'use strict';

require('dotenv').config();

const axios = require('axios');
const fs = require('fs');
const yaml = require('js-yaml');
const Mustache = require('mustache');
const merge = require('deepmerge-json');
const CryptoJS = require("crypto-js");

const passwordGenerator = require('generate-password');
const functions = require('@google-cloud/functions-framework');
const {createLogger, format, transports} = require('winston');
const {LoggingWinston} = require('@google-cloud/logging-winston');
const loggingWinston = new LoggingWinston();

const {Validator, ValidationError} = require("jsonschema");
const v = new Validator();
const schema = require('./schemas/request-body.json');
const {Firestore} = require('@google-cloud/firestore');
const https = require("https");

let logger = null;

const httpsAgent = new https.Agent({
  // Trust self-signed certificates
  rejectUnauthorized: false
});

const FIRESTORE_STATUS = {
  CREATED: "created",
  CREATION_FAILED: "creation_failed",
  CREATION_IN_PREPARATION: 'creation_in_preparation',
  CREATION_IN_PROGRESS: "creation_in_progress",
};


function initializeLogger(gcpProjectId, logLevel, instanceName) {
  logger = createLogger({
    level: logLevel,
    defaultMeta: {
      function: process.env.K_SERVICE || 'timmy-create-tenant',
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
  logger.info(`Authenticating with ${username} username to ArgoCD server ${url} to get a token`);
  const resourceUrl = new URL('/api/v1/session', url);
  const payload = JSON.stringify({username: username, password: password});
  const config = {httpsAgent: httpsAgent, headers: {'Content-Type': 'application/json'}};

  try {
    const resp = await axios.post(resourceUrl.toString(), payload, config);
    const token = resp.data.token
    logger.debug(`Token: ${token}`);
    if (!token) {
      const msg = 'Failed to authenticate to ArgoCD due to undefined token';
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
 * Template the ArgoCD YAML manifest for the tenant
 * @param params An object containing all the parameters for the template
 * @returns {String} The templated YAML manifest
 */
function templateArgoCdManifest(params) {
  try {
    logger.info('Template ArgoCD manifest for the tenant');
    const template = fs.readFileSync("templates/argocd-application.mustache").toString();
    const rendered = Mustache.render(template, params);
    logger.debug(`Rendered ArgoCD YAML manifest: ${rendered}`);
    return rendered;
  } catch (error) {
    logger.error(`Failed to template the ArgoCD application manifest: ${error}`);
    throw new Error('Failed templating ArgoCD manifest');
  }
}

/**
 * Cast YAML to JSON format
 * @param content The YAML content to convert into JSON
 * @returns {string} The converted content into JSON
 */
function castYamlToJson(content) {
  try {
    logger.info('Convert ArgoCD application manifest to JSON document');
    const renderedManifestYaml = yaml.load(content, 'utf8');
    const payload = JSON.stringify(renderedManifestYaml, null, 2);
    logger.debug(`The ArgoCD JSON document: ${JSON.stringify(payload)}`);
    return payload;
  } catch (err) {
    logger.error(`Failed to convert ArgoCD manifest to JSON: ${err}`);
    throw new Error('Failed to convert ArgoCD manifest to JSON');
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
  logger.info('Create the ArgoCD application for the new tenant');
  const resourceUrl = new URL('/api/v1/applications', url);
  const config = {
    httpsAgent: httpsAgent,
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  };

  try {
    return Promise.resolve(await axios.post(resourceUrl.toString(), payload, config));
  } catch (error) {
    const msg = formatAxiosError('Failed to create the ArgoCD application', error);
    logger.error(msg);
    return Promise.reject(msg);
  }
}

/**
 * Ensure the ArgoCD application is healthy
 * @param url the ArgoCD server base url
 * @param token a token to authenticate to the ArgoCD server
 * @param appName the ArgoCD application name
 * @param maxRetries the maximum number of attempts
 * @param retryInterval time between each attempt
 * @returns {Promise<unknown>}
 */
async function ensureArgoCdAppIsHealthy(url, token, appName, maxRetries = 60, retryInterval = 10) {
  const path = `/api/v1/applications/${appName}`;
  const resourceUrl = new URL(path, url).toString();
  const config = {
    httpsAgent: httpsAgent,
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  };

  const sleep = (milliseconds) => {
    return new Promise(resolve => setTimeout(resolve, milliseconds))
  };

  const HEALTH_STATUS = {
    HEALTHY: 'Healthy',
    DEGRADED: 'Degraded'
  };

  let currentRetry = 1;
  let resp;
  let healthStatus;
  let msg;

  try {
    logger.info('Verify that the the ArgoCD application is healthy');
    resp = await axios.get(resourceUrl, config);
    healthStatus = resp['data']['status']['health']['status'];
  } catch (error) {
    msg = formatAxiosError('Failed to get the status of the ArgoCD application', error);
    logger.error(msg);
    return Promise.reject(msg);
  }

  while (healthStatus !== HEALTH_STATUS.HEALTHY && currentRetry <= maxRetries) {

    try {
      logger.info(`The ArgoCD application is being created and not healthy (HEALTH_STATUS: ${healthStatus}). Next check in ${retryInterval} seconds (${currentRetry}/${maxRetries} retries)`);
      await sleep(retryInterval * 1000);

      resp = await axios.get(resourceUrl, config);
      healthStatus = resp['data']['status']['health']['status'];

      if (healthStatus === HEALTH_STATUS.HEALTHY) {
        logger.info('The ArgoCD application is healthy');
        return Promise.resolve();
      }

      if (healthStatus === HEALTH_STATUS.DEGRADED) {
        const msg = resp['data']['status']['operationState']['message'];
        return Promise.reject(new Error(`The ArgoCD application health is degraded: ${msg}. Please check the ArgoCD application at ${url}/applications/${appName}`));
      }

      currentRetry++;
    } catch (error) {
      msg = formatAxiosError('Failed to follow the progression of the ArgoCD application health', error);
      logger.error(msg);
      return Promise.reject(msg);
    }

  }

  msg = `Exceeded maximum attempts to ensure healthiness, please check the ArgoCD application status at ${url}/applications/${appName}`;
  logger.error(msg);
  return Promise.reject(msg);
}

async function ensureArgoCdAppIsSynced(url, token, appName, maxRetries = 20, retryInterval = 10) {
  const path = `/api/v1/applications/${appName}`
  const resourceUrl = new URL(path, url).toString();
  const config = {
    httpsAgent: httpsAgent,
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  };

  const sleep = (milliseconds) => {
    return new Promise(resolve => setTimeout(resolve, milliseconds))
  };

  const SYNC_STATUS = {
    SYNCED: 'Synced',
    OUT_OF_SYNC: 'OutOfSync'
  }


  let currentRetry = 1;
  let resp;
  let syncStatus;
  let msg;

  try {
    logger.info('Verify that the the ArgoCD application is fully synced');
    resp = await axios.get(resourceUrl, config);
    syncStatus = resp['data']['status']['sync']['status'];
  } catch (error) {
    msg = formatAxiosError('Failed to retrieve the ArgoCD application sync status', error)
    logger.error(msg);
    return Promise.reject(msg);
  }


  while (syncStatus !== SYNC_STATUS.SYNCED && currentRetry <= maxRetries) {
    try {
      logger.info(`The ArgoCD application is not fully synced (SYNC_STATUS: ${syncStatus}). Next check in ${retryInterval} seconds (${currentRetry}/${maxRetries} retries)`);
      await sleep(retryInterval * 1000);

      resp = await axios.get(resourceUrl, config);
      syncStatus = resp['data']['status']['sync']['status'];

      if (syncStatus === syncStatus.SYNCED) {
        logger.info('The ArgoCD application is fully synced');
        return Promise.resolve();
      }

      currentRetry++;
    } catch (error) {
      msg = formatAxiosError(`Failed to check the progression of the ArgoCD application sync status, please check ArgoCD application status at ${url}/applications/${appName}`, error);
      logger.error(msg);
      return Promise.reject(msg);
    }

  }

  msg = `Exceeded maximum attempts to ensure synchronization, please check the ArgoCD application status at ${url}/applications/${appName}`;

  logger.error(msg);
  return Promise.reject(msg);
}

/**
 * Generate a password string
 * @param length the number of characters of the password
 * @param numbers enable numbers in the password
 * @param lowercase enable lowercase character
 * @param uppercase enable uppercase character
 * @param symbols enable special symbols
 * @returns {string} password
 */
function generatePassword(length = 16, numbers = true, lowercase = true, uppercase = true, symbols = false) {
  return passwordGenerator.generate({
    length: length,
    numbers: numbers,
    lowercase: lowercase,
    uppercase: uppercase,
    symbols: symbols
  });
}

async function encryptAES(text, key) {
  try {
    logger.debug(`Encrypt text with ${process.env.TENANT_CONTEXT_ENCRYPT_KEY} key`);
    return await CryptoJS.AES.encrypt(text, key).toString();
  } catch (error) {
    logger.debug(`Failed to encrypt text with ${key} key`);
    return Promise.reject(error);
  }
}

/**
 * Update firestore document in collection if exists otherwise create it
 * @param firestore firestore client instance
 * @param collection collection for the document
 * @param doc name of the document
 * @param status value of the status field
 * @param context object representing the context field
 * @param encrypted encrypt the firestore document
 */
async function updateFirestoreDoc(firestore, collection, doc, status, context) {
  logger.info(`Update the \`${doc}\` Firestore document in \`${collection}\` collection with \`${status}\` status and tenant context`);
  let data = {
    status: status,
    status_date: new Date().toISOString(),
    context: context
  };

  logger.debug(`Prepared Firestore document: ${JSON.stringify(data)}`);

  if (process.env.TENANT_CONTEXT_ENCRYPT_KEY) {
    try {
      data.context = await encryptAES(JSON.stringify(data.context), process.env.TENANT_CONTEXT_ENCRYPT_KEY);
    } catch (error) {
      const msg = `Failed to encrypt \`${doc}\` Firestore document in \`${collection}\ collection: ${error}`;
      logger.error(msg);
      return Promise.reject(msg);
    }
  }

  try {
    await firestore.collection(collection).doc(doc).set(data);
  } catch (error) {
    const msg = `Failed to update \`${doc}\` Firestore document in \`${collection}\` collection: ${error}`;
    logger.error(msg);
    return Promise.reject(msg);
  }

  logger.debug(`Updated Firestore document`);
}


/**
 * create firestore document in the collection if it doesn't exist , otherwise logg a message
 * @param firestore firestore client instance
 * @param collection collection for the document
 * @param doc name of the document
 * @param status value of the status field
 * @param encrypted encrypt the firestore document
 */
 async function createFirestoreDoc(firestore, collection, docRef, status) {
  logger.info(`Create the \`${docRef}\` Firestore document in \`${collection}\` collection with \`${status}\` status`);
  let data = {
    status: status,
    status_date: new Date().toISOString(),
    context: {}
  };

  logger.debug(`Prepared Firestore document: ${JSON.stringify(data)}`);

  try {
    let document = firestore.collection(collection).doc(docRef);
    const snapshot = await document.get();
    if (snapshot.exists){
      let msg = "The document "+ docRef +" already exists !!!";
      logger.error(msg);
      return Promise.reject(msg);

    }else{
      // add the new document.
      await firestore.collection(collection).doc(docRef).set(data);
    }
  } catch (error) {
    const msg = `Failed to create  \`${docRef}\` Firestore document in \`${collection}\` collection: ${error}`;
    logger.error(msg);
    return Promise.reject(msg);
  }

  logger.info("the document for the: "+docRef+" created with success !!!");
}


async function updateFirestoreDocStatus(firestore, collection, doc, status) {
  try {
    logger.info(`Update the \`${doc}\` firestore document in \`${collection}\` collection with \`${status}\` status`);
    return Promise.resolve(await firestore.collection(collection).doc(doc).set({
      status: status,
      status_date: new Date().toISOString()
    }, {merge: true}));
  } catch (error) {
    const msg = `Failed to update the \`${doc}\` firestore document in \`${collection}\` collection: ${error}`;
    logger.error(msg);
    return Promise.reject(msg);
  }
}

/**
 * Ensure environment variable is not missing or undefined
 * @param name The name of the environment variable
 * @returns {string} The value of the environment variable
 */
function loadEnvironmentVariable(name) {
  if (!process.env[name]) {
    const msg = `The environment variable ${name} is missing or undefined`;
    throw new Error(msg);
  }
  return process.env[name];
}

functions.http('createTenant', (req, res) => {
    const ARGOCD_PASSWORD = loadEnvironmentVariable('ARGOCD_PASSWORD');
    const ARGOCD_URL = new URL(loadEnvironmentVariable('ARGOCD_URL')).toString();
    const ARGOCD_USERNAME = loadEnvironmentVariable('ARGOCD_USERNAME');
    const GCP_FIRESTORE_PROJECT_ID = loadEnvironmentVariable('GCP_FIRESTORE_PROJECT_ID');
    const GCP_PROJECT_ID = loadEnvironmentVariable('GCP_PROJECT_ID');
    const GOOGLE_ZONE = loadEnvironmentVariable('GOOGLE_ZONE');
    const LOG_LEVEL = loadEnvironmentVariable('LOG_LEVEL');
    const MAILER_API_KEY = loadEnvironmentVariable('MAILER_API_KEY');
    const MAILER_BASE_URL = loadEnvironmentVariable('MAILER_BASE_URL');
    const MAILER_DOMAIN = loadEnvironmentVariable('MAILER_DOMAIN');
    const PIM_IMAGE_REPOSITORY = loadEnvironmentVariable('PIM_IMAGE_REPOSITORY');
    const PIM_IMAGE_TAG = loadEnvironmentVariable('PIM_IMAGE_TAG');
    const SOURCE_PATH = loadEnvironmentVariable('SOURCE_PATH');
    const SOURCE_REPO_URL = loadEnvironmentVariable('SOURCE_REPO_URL');
    const SOURCE_TARGET_REVISION = loadEnvironmentVariable('SOURCE_TARGET_REVISION');
    const TENANT_CONTEXT = loadEnvironmentVariable('TENANT_CONTEXT');

    const body = JSON.parse(req.body);

    initializeLogger(GCP_PROJECT_ID, LOG_LEVEL, body.instanceName);

    // Ensure the json object in the http request body respects the expected schema
    logger.info('Validation of the JSON schema of the request body');
    logger.debug(`HTTP request JSON body: ${JSON.stringify(req.body)}`);

    const schemaCheck = v.validate(body, schema);
    if (!schemaCheck.valid) {
      const error = schemaCheck.errors[0].message;
      res.status(400).json({
        status_code: 400,
        message: `HTTP body json is not valid: ${error}`,
      })
      throw new Error(`The JSON schema of the received http body is not valid: ${error}`);
    }
    logger.debug(`Received HTTP json body: ${JSON.stringify(body)}`);

    const instanceName = body.instanceName;
    const dnsCloudDomain = body.dnsCloudDomain;
    const extraLabelType = 'ucs';
    const pfid = `${extraLabelType}-${instanceName}`;
    const pimMasterDomain = `${instanceName}.${dnsCloudDomain}`;

    logger.debug(`Initialize the firestore client with instance in ${GCP_FIRESTORE_PROJECT_ID} project`);
    const firestore = new Firestore({
      projectId: GCP_FIRESTORE_PROJECT_ID,
      timestampsInSnapshots: true
    });


    const prepareTenantCreation = async () => {
        await createFirestoreDoc(firestore, TENANT_CONTEXT, instanceName, FIRESTORE_STATUS.CREATION_IN_PREPARATION);
        logger.info('Generate tenant credentials');
        const mailerPassword = generatePassword();
        logger.debug(`mailerPassword: ${mailerPassword}`);
        const defaultAdminUserPassword = generatePassword();
        logger.debug(`defaultAdminUserPassword: ${defaultAdminUserPassword}`);
        const pimSecret = generatePassword();
        logger.debug(`pimSecret: ${pimSecret}`);
        const pimMonitoringToken = generatePassword();
        logger.debug(`pimMonitoringToken: ${pimMonitoringToken}`);
        const mysqlUserPassword = generatePassword();
        logger.debug(`mysqlUserPassword: ${mysqlUserPassword}`);
        const mysqlRootPassword = generatePassword()
        logger.debug(`mysqlRootPassword: ${mysqlRootPassword}`);

        // Deep merge of the request json body and the computed json object
        const parameters = merge(body, {
          source: {
            repoUrl: SOURCE_REPO_URL,
            path: SOURCE_PATH,
            targetRevision: SOURCE_TARGET_REVISION
          },
          destination: {
            server: 'https://kubernetes.default.svc',
            namespace: instanceName
          },
          backup: {
            enabled: false
          },
          common: {
            gcpProjectID: GCP_PROJECT_ID,
            gcpFireStoreProjectID: GCP_FIRESTORE_PROJECT_ID,
            googleZone: GOOGLE_ZONE,
            pimMasterDomain: pimMasterDomain,
            dnsCloudDomain: dnsCloudDomain,
            workloadIdentityGSA: 'main-service-account',
            workloadIdentityKSA: `${pfid}-ksa-workload-identity`,
          },
          elasticsearch: {
            client: {
              heapSize: "128m",
              resources: {
                requests: {
                  cpu: "20m",
                  memory: "1024Mi"
                },
                limits: {
                  cpu: "1",
                  memory: "1024Mi"
                }
              }
            },
            master: {
              heapSize: "512m",
              resources: {
                requests: {
                  cpu: "15m",
                  memory: "768Mi"
                },
                limits: {
                  cpu: "1",
                  memory: "768Mi"
                }
              }
            },
            data: {
              heapSize: "1024m",
              resources: {
                requests: {
                  cpu: "40m",
                  memory: "1536Mi"
                },
                limits: {
                  cpu: "1",
                  memory: "1740Mi"
                }
              }
            },
          },
          global: {
            extraLabels: {
              instanceName: instanceName,
              pfid: pfid,
              instance_dns_zone: dnsCloudDomain,
              instance_dns_record: pimMasterDomain,
              papo_project_code: instanceName,
              papo_project_code_truncated: instanceName,
              papo_project_code_hashed: instanceName,
              type: extraLabelType,
            }
          },
          image: {
            pim: {
              repository: PIM_IMAGE_REPOSITORY,
              tag: PIM_IMAGE_TAG,
            }
          },
          mailer: {
            login: `${instanceName}@${MAILER_DOMAIN}`,
            password: mailerPassword,
            base_mailer_url: MAILER_BASE_URL,
            domain: MAILER_DOMAIN,
            api_key: MAILER_API_KEY,
          },
          memcached: {
            resources: {
              limits: {
                cpu: "1",
                memory: "32Mi"
              },
              requests: {
                cpu: "25m",
                memory: "16Mi"
              }
            }
          },
          mysql: {
            mysql: {
              userPassword: mysqlUserPassword,
              rootPassword: mysqlRootPassword,
              dataDiskSize: "10",
              innodbBufferPoolSize: "2G",
              resources: {
                limits: {
                  cpu: "1",
                  memory: "3584Mi"
                },
                requests: {
                  cpu: "100m",
                  memory: "3584Mi"
                }
              }
            },
            common: {
              class: "ssd-retain-csi",
              persistentDisks: [
                `projects/${GCP_PROJECT_ID}/zones/${GOOGLE_ZONE}/disks/${pfid}-mysql`
              ]
            }
          },
          pim: {
            storage: {
              bucketName: pfid
            },
            defaultAdminUser: {
              password: defaultAdminUserPassword
            },
            secret: pimSecret,
            monitoring: {
              authenticationToken: pimMonitoringToken
            },
          }
        });
        logger.debug(`Prepared data for tenant creation: ${JSON.stringify(parameters)}`);
        return parameters;
      }
    ;

    const createTenant = async () => {
      const parameters = await prepareTenantCreation();
      await updateFirestoreDoc(firestore, TENANT_CONTEXT, instanceName, FIRESTORE_STATUS.CREATION_IN_PROGRESS, {
        AKENEO_PIM_URL: `https://${instanceName}.${parameters.pim.dnsCloudDomain}`,
        APP_DATABASE_HOST: `pim-mysql.${pfid}.svc.cluster.local`,
        APP_DATABASE_PASSWORD: parameters.mysql.mysql.userPassword,
        APP_INDEX_HOSTS: `elasticsearch-client.${pfid}.svc.cluster.local`,
        APP_SECRET: parameters.pim.secret,
        APP_TENANT_ID: pfid,
        DATABASE_ROOT_PASSWORD: parameters.mysql.mysql.rootPassword,
        MAILER_PASSWORD: parameters.mailer.password,
        MAILER_URL: parameters.mailer.base_mailer_url,
        MEMCACHED_SVC: `memcached.${pfid}.svc.cluster.local`,
        MONITORING_AUTHENTICATION_TOKEN: parameters.pim.monitoring.authenticationToken,
        PFID: pfid,
        SRNT_GOOGLE_BUCKET_NAME: pfid
      });

      const manifest = templateArgoCdManifest(parameters);
      const payload = castYamlToJson(manifest);
      const token = await getArgoCdToken(ARGOCD_URL, ARGOCD_USERNAME, ARGOCD_PASSWORD);
      const resp = await createArgoCdApp(ARGOCD_URL, token, payload);
      await ensureArgoCdAppIsHealthy(ARGOCD_URL, token, instanceName);
      // TODO PH-286: full synced is not possible because http routes objects are not synced. Fix that to uncomment this line
      await ensureArgoCdAppIsSynced(ARGOCD_URL, token, instanceName);
    }

    createTenant(res)
      .then(async () => {
        // TODO: only update status field when decryption is released (PH-247)
        await updateFirestoreDocStatus(firestore, TENANT_CONTEXT, instanceName, FIRESTORE_STATUS.CREATED);

        logger.info('Tenant is created');

        // TODO : notify the portal with 'activated' status
        res.status(200).json({
          status_code: 200,
          message: `Successfully created the tenant ${instanceName}`
        })
      })
      .catch(async (error) => {
        logger.error(error);
        // TODO: only update status field when decryption is released (PH-247)
        await updateFirestoreDocStatus(firestore, TENANT_CONTEXT, instanceName, FIRESTORE_STATUS.CREATION_FAILED);

        res.status(500).json({
          status_code: 500,
          message: `Failed to create the tenant ${instanceName}: ${error}`
        })
      });
  }
)
;
