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
const fs = require('fs');
const yaml = require('js-yaml');
const Mustache = require('mustache');
const merge = require('deepmerge-json');

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

const httpsAgent = new https.Agent({
  // Trust self-signed certificates
  rejectUnauthorized: false
});

const DEFAULT_BRANCH_NAME = 'master';
const DEFAULT_PIM_NAMESPACE = 'pim';
const FIRESTORE_STATUS = {
  CREATED: "created",
  CREATION_FAILED: "creation_failed",
  CREATION_IN_PREPARATION: 'creation_in_preparation',
  CREATION_IN_PROGRESS: "creation_in_progress",
  DELETED: 'deleted',
};

let firestoreCollection = null;
let logger = null;

function initializeLogger(branchName, tenantId) {
  logger = createLogger({
    level: process.env.LOG_LEVEL,
    defaultMeta: {
      id: crypto.randomUUID(),
      function: process.env.K_SERVICE || 'timmy-create-tenant',
      revision: process.env.K_REVISION,
      gcpProjectId: process.env.GCP_PROJECT_ID,
      gcpProjectFirestoreId: process.env.GCP_FIRESTORE_PROJECT_ID,
      branchName: branchName,
      tenant: tenantId
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
          branchName: info.branchName,
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
  logger.info(`Authenticating with ${process.env.ARGOCD_USERNAME} username to ArgoCD server ${process.env.ARGOCD_URL} to get a token`);
  const url = new URL('/api/v1/session', process.env.ARGOCD_URL);
  const payload = JSON.stringify({username: process.env.ARGOCD_USERNAME, password: process.env.ARGOCD_PASSWORD});
  const config = {httpsAgent: httpsAgent, headers: {'Content-Type': 'application/json'}};

  try {
    const resp = await axios.post(url.href.toString(), payload, config);
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
async function createArgoCdApp(token, payload) {
  logger.info('Create the ArgoCD application for the new tenant');
  const url = new URL('/api/v1/applications?upsert=true', process.env.ARGOCD_URL);
  const config = {
    httpsAgent: httpsAgent,
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  };

  try {
    return Promise.resolve(await axios.post(url.href.toString(), payload, config));
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
async function ensureArgoCdAppIsHealthy(token, appName, maxRetries = 60, retryInterval = 10) {
  const url = new URL(`/api/v1/applications/${appName}`, process.env.ARGOCD_URL);
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

  let retriedAfterUnhealthy = false;
  let currentRetry = 1;
  let resp;
  let healthStatus;
  let msg;

  try {
    logger.info('Verify that the the ArgoCD application is healthy');
    resp = await axios.get(url.href.toString(), config);
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

      resp = await axios.get(url.href.toString(), config);
      healthStatus = resp['data']['status']['health']['status'];

      if (healthStatus === HEALTH_STATUS.HEALTHY) {
        logger.info('The ArgoCD application is healthy');
        return Promise.resolve();
      }

      if (healthStatus === HEALTH_STATUS.DEGRADED) {
        const msg = resp['data']['status']['operationState']['message'];
        if (retriedAfterUnhealthy) {
          return Promise.reject(new Error(`The ArgoCD application health is degraded: ${msg}. Please check the ArgoCD application at ${url}/applications/${appName}`));
        }
        retriedAfterUnhealthy = true;
        // Wait 30s if the status is unhealthy the first time
        await sleep(1000 * 30);
      }

      currentRetry++;
    } catch (error) {
      msg = formatAxiosError('Failed to follow the progression of the ArgoCD application health', error);
      logger.error(msg);
      return Promise.reject(msg);
    }
  }

  if (healthStatus !== HEALTH_STATUS.HEALTHY) {
    msg = `Exceeded maximum attempts to ensure healthiness, please check the ArgoCD application status at ${url}/applications/${appName}`;
    logger.error(msg);
    return Promise.reject(msg);
  }

  return Promise.resolve();
}

async function ensureArgoCdAppIsSynced(token, appName, maxRetries = 20, retryInterval = 10) {
  const url = new URL(`/api/v1/applications/${appName}`, process.env.ARGOCD_URL);
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
    resp = await axios.get(url.href.toString(), config);
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

      resp = await axios.get(url.href.toString(), config);
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

  if (syncStatus !== SYNC_STATUS.SYNCED) {
    msg = `Exceeded maximum attempts to ensure synchronization, please check the ArgoCD application status at ${url}/applications/${appName}`;
    logger.error(msg);
    return Promise.reject(msg);
  }

  return Promise.resolve();
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
    logger.debug(`Encrypt text with ${process.env.TENANT_CONTEXT_ENCRYPTION_KEY} key`);

    const algorithm = 'aes-256-cbc'
    const initVector = crypto.randomBytes(16)
    logger.debug(`iv = ${initVector}`)

    const securityKey = Buffer.from(process.env.TENANT_CONTEXT_ENCRYPTION_KEY, 'base64')
    logger.debug(`securityKey = ${securityKey}`)

    const cipher = crypto.createCipheriv(algorithm, securityKey, initVector)

    let encryptedData = cipher.update(text, "utf-8", "base64")
    encryptedData += cipher.final("base64")

    const encryptedPayload = JSON.stringify({
      data: encryptedData,
      iv: initVector.toString('hex'),
    })

    logger.debug('Context values encryption successful')
    logger.debug(encryptedPayload)

    return encryptedPayload
  } catch (error) {
    logger.debug(`Failed to encrypt text with ${key} key`);
    return Promise.reject(error);
  }
}

/**
 * Update firestore document in collection if exists otherwise create it
 * @param firestore firestore client instance
 * @param docRef document reference
 * @param status value of the status field
 * @param context object representing the context field
 */
async function updateFirestoreDoc(firestore, docRef, status, context) {
  logger.info(`Update the \`${docRef}\` Firestore document in \`${firestoreCollection}\` collection with \`${status}\` status and tenant context`);
  let data = {
    status: status,
    status_date: new Date().toISOString(),
    context: context
  };

  logger.debug(`Prepared Firestore document: ${JSON.stringify(data)}`);

  if (process.env.TENANT_CONTEXT_ENCRYPTION_KEY) {
    try {
      data.context = await encryptAES(JSON.stringify(data.context), process.env.TENANT_CONTEXT_ENCRYPTION_KEY);
    } catch (error) {
      const msg = `Failed to encrypt \`${docRef}\` Firestore document in \`${firestoreCollection}\` collection: ${error}`;
      logger.error(msg);
      return Promise.reject(msg);
    }
  }

  try {
    await firestore.collection(firestoreCollection).doc(docRef).set(data);
  } catch (error) {
    const msg = `Failed to update \`${docRef}\` Firestore document in \`${firestoreCollection}\` collection: ${error}`;
    logger.error(msg);
    return Promise.reject(msg);
  }

  logger.debug(`Updated Firestore document`);
}

/**
 * create firestore document in the collection if it doesn't exist , otherwise logg a message
 * @param firestore firestore client instance
 * @param docRef document reference
 * @param status value of the status field
 */
async function createFirestoreDoc(firestore, docRef, status) {
  logger.info(`Create the \`${docRef}\` Firestore document in \`${firestoreCollection}\` collection with \`${status}\` status`);
  let data = {
    status: status,
    status_date: new Date().toISOString(),
    context: {}
  };

  logger.debug(`Prepared Firestore document: ${JSON.stringify(data)}`);

  try {
    let document = firestore.collection(firestoreCollection).doc(docRef);
    const snapshot = await document.get();
    if (snapshot.exists && snapshot.data().status !== FIRESTORE_STATUS.DELETED) {
      let msg = `The document ${docRef} already exists and not with deleted status (actually ${snapshot.data().status} status)!!!`;
      logger.error(msg);
      return Promise.reject(msg);
    } else {
      // add the new document.
      await firestore.collection(firestoreCollection).doc(docRef).set(data);
    }
  } catch (error) {
    const msg = `Failed to create  \`${docRef}\` Firestore document in \`${firestoreCollection}\` collection: ${error}`;
    logger.error(msg);
    return Promise.reject(msg);
  }

  logger.info("the document for the: " + docRef + " created with success !!!");
}


async function updateFirestoreDocStatus(firestore, docRef, status) {
  try {
    logger.info(`Update the \`${docRef}\` firestore document in \`${firestoreCollection}\` collection with \`${status}\` status`);
    return Promise.resolve(await firestore.collection(firestoreCollection).doc(docRef).set({
      status: status,
      status_date: new Date().toISOString()
    }, {merge: true}));
  } catch (error) {
    const msg = `Failed to update the \`${docRef}\` firestore document in \`${firestoreCollection}\` collection: ${error}`;
    logger.error(msg);
    return Promise.reject(msg);
  }
}

functions.http('createTenant', (req, res) => {
    requiredEnvironmentVariables([
      'ARGOCD_PASSWORD',
      'ARGOCD_URL',
      'ARGOCD_USERNAME',
      'GCP_FIRESTORE_PROJECT_ID',
      'GCP_PROJECT_ID',
      'GOOGLE_ZONES',
      'LOG_LEVEL',
      'MAILER_API_KEY',
      'MAILER_DOMAIN',
      'REGION',
      'SOURCE_PATH',
      'SOURCE_REPO_URL',
      'TENANT_CONTEXT_COLLECTION_NAME',
    ]);

    const body = req.body;
    // If branchName is an empty string it is the default branch
    const branchName = body.branchName
    const tenantName = body.tenant_name;
    const extraLabelType = 'srnt';
    const tenantId = `${extraLabelType}-${tenantName}`;

    // Workarround to UAT migration
    const pimNamespaceOld = (branchName === DEFAULT_BRANCH_NAME ? DEFAULT_PIM_NAMESPACE : DEFAULT_PIM_NAMESPACE + "-" + branchName.toLowerCase());
    const pimNamespace = (pimNamespaceOld === "pim-master-migration-step2" ? DEFAULT_PIM_NAMESPACE : pimNamespaceOld);

    firestoreCollection = `${process.env.REGION}/${pimNamespace}/${process.env.TENANT_CONTEXT_COLLECTION_NAME}`;

    initializeLogger(branchName, tenantId);

    // Ensure the json object in the http request body respects the expected schema
    logger.debug('Validation of the JSON schema of the request body');
    logger.debug(`HTTP request JSON body: ${JSON.stringify(req.body)}`);

    const schemaCheck = v.validate(body, schema);
    if (!schemaCheck.valid) {
      const error = schemaCheck.errors[0].message;
      res.status(400).json({
        status_code: 400,
        message: `HTTP body json is not valid: ${error}`,
      })
    }

    const dnsCloudDomain = body.dnsCloudDomain;
    const pimEdition = body.pim_edition;
    const fqdn = `${tenantName}.${dnsCloudDomain}`;

    logger.debug('Initialize the firestore client');
    const firestore = new Firestore({
      projectId: process.env.GCP_FIRESTORE_PROJECT_ID,
      timestampsInSnapshots: true
    });


    const prepareTenantCreation = async () => {
        await createFirestoreDoc(firestore, tenantId, FIRESTORE_STATUS.CREATION_IN_PREPARATION);
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
        const zones = process.env.GOOGLE_ZONES.split(",");
        const googleZone = zones[Math.floor(Math.random()*zones.length)]
        logger.debug(`googleZone: ${googleZone}`);

        // Deep merge of the request json body and the computed json object
        const parameters = merge(body, {
          source: {
            repoUrl: process.env.SOURCE_REPO_URL,
            path: process.env.SOURCE_PATH,
            targetRevision: branchName
          },
          destination: {
            server: 'https://kubernetes.default.svc',
            namespace: tenantId
          },
          backup: {
            enabled: false
          },
          common: {
            gcpProjectID: process.env.GCP_PROJECT_ID,
            gcpFireStoreProjectID: process.env.GCP_FIRESTORE_PROJECT_ID,
            googleZone: googleZone,
            fqdn: fqdn,
            dnsCloudDomain: dnsCloudDomain,
            workloadIdentityKSA: `${tenantName}-ksa-workload-identity`,
            tenantContext: firestoreCollection,
          },
          elasticsearch: {
            client: {
              heapSize: "128m",
              resources: {
                requests: {
                  cpu: "20m",
                  memory: "160Mi"
                },
                limits: {
                  memory: "1024Mi"
                }
              }
            },
            master: {
              heapSize: "384m",
              resources: {
                requests: {
                  cpu: "15m",
                  memory: "768Mi"
                },
                limits: {
                  memory: "768Mi"
                }
              }
            },
            data: {
              heapSize: "1024m",
              resources: {
                requests: {
                  cpu: "40m",
                  memory: "768Mi"
                },
                limits: {
                  memory: "1740Mi"
                }
              }
            },
            serviceAccounts: {
              client: {
                name: `${tenantName}-ksa-workload-identity`
              },
              master: {
                name: `${tenantName}-ksa-workload-identity`
              },
              data: {
                name: `${tenantName}-ksa-workload-identity`
              }
            },
            snapshots: {
              bucketName: `pim-${tenantName}-es`
            },
          },
          global: {
            extraLabels: {
              instance_dns_record: fqdn,
              instance_dns_zone: dnsCloudDomain,
              papo_project_code: tenantName,
              papo_project_code_hashed: tenantName,
              papo_project_code_truncated: tenantName,
              tenant_id: tenantId,
              tenant_name: tenantName,
              type: extraLabelType,
            }
          },
          mailer: {
            login: `${tenantName}@${process.env.MAILER_DOMAIN}`,
            password: mailerPassword,
            from: `Akeneo <no-reply@${fqdn}>`,
            baseMailerDsn: `smtp://${tenantName}@${process.env.MAILER_DOMAIN}:${mailerPassword}@smtp.mailgun.org:2525`,
            domain: process.env.MAILER_DOMAIN,
            apiKey: process.env.MAILER_API_KEY,
          },
          mysql: {
            mysql: {
              userPassword: mysqlUserPassword,
              rootPassword: mysqlRootPassword,
              innodbBufferPoolSize: "2048M",
              resources: {
                limits: {
                  memory: "3584Mi"
                },
                requests: {
                  cpu: "200m",
                  memory: "768Mi"
                }
              }
            },
            common: {
              class: "ssd-retain-csi",
              persistentDisks: [
                `projects/${process.env.GCP_PROJECT_ID}/zones/${googleZone}/disks/${tenantId}-mysql`
              ]
            }
          },
          pim: {
            storage: {
              bucketName: `pim-${tenantName}`,
              location: (process.env.REGION).toUpperCase()
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
      await updateFirestoreDoc(firestore, tenantId, FIRESTORE_STATUS.CREATION_IN_PROGRESS, {
        AKENEO_PIM_URL: `https://${tenantName}.${parameters.common.dnsCloudDomain}`,
        APP_DATABASE_HOST: `pim-mysql.${tenantId}.svc.cluster.local`,
        APP_DATABASE_PASSWORD: parameters.mysql.mysql.userPassword,
        APP_INDEX_HOSTS: `elasticsearch-client.${tenantId}.svc.cluster.local`,
        APP_SECRET: parameters.pim.secret,
        APP_TENANT_ID: tenantId,
        DATABASE_ROOT_PASSWORD: parameters.mysql.mysql.rootPassword,
        MAILER_PASSWORD: parameters.mailer.password,
        MAILER_DSN: parameters.mailer.baseMailerDsn,
        MAILER_FROM: parameters.mailer.from,
        MONITORING_AUTHENTICATION_TOKEN: parameters.pim.monitoring.authenticationToken,
        PFID: tenantId,
        PIM_EDITION: pimEdition,
        SRNT_GOOGLE_BUCKET_NAME: `pim-${tenantName}`
      });

      const manifest = templateArgoCdManifest(parameters);
      const payload = castYamlToJson(manifest);
      const token = await getArgoCdToken();
      await createArgoCdApp(token, payload);
      await ensureArgoCdAppIsHealthy(token, tenantId);
      await ensureArgoCdAppIsSynced(token, tenantId);
    }

    createTenant(res)
      .then(async () => {
        await updateFirestoreDocStatus(firestore, tenantId, FIRESTORE_STATUS.CREATED);

        logger.info('Tenant is created');

        // TODO : notify the portal with 'activated' status
        res.status(200).json({
          status_code: 200,
          message: `Successfully created the tenant ${tenantId}`
        })
      })
      .catch(async (error) => {
        logger.error(error);
        // TODO: only update status field when decryption is released (PH-247)
        await updateFirestoreDocStatus(firestore, tenantId, FIRESTORE_STATUS.CREATION_FAILED);

        res.status(500).json({
          status_code: 500,
          message: `Failed to create the tenant ${tenantId}: ${error}`
        })
      });
  }
);
