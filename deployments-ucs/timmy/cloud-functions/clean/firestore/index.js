/**
 * Responds to any HTTP request.
 *
 * @param {!express:Request} req HTTP request context.
 * @param {!express:Response} res HTTP response context.
 */

const {Firestore} = require('@google-cloud/firestore');
const functions = require('@google-cloud/functions-framework');
const path = require('path');
const axios = require('axios');
const crypto = require('crypto');
const https = require("https");
const {createLogger, format, transports} = require('winston');
const {LoggingWinston} = require('@google-cloud/logging-winston');
const loggingWinston = new LoggingWinston();

let logger = null;
let argoCdClient = null;

/**
 * Initialize the logger for the cloud function
 */
function initializeLogger() {
  logger = createLogger({
    level: process.env.LOG_LEVEL,
    defaultMeta: {
      id: crypto.randomUUID(),
      function: process.env.K_SERVICE || 'timmy-clean-firestore',
      revision: process.env.K_REVISION,
      gcpProjectId: process.env.GCP_PROJECT_ID,
      gcpProjectFirestoreId: process.env.GCP_FIRESTORE_PROJECT_ID,
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

/**
 * Get a token from the ArgoCD server
 * @param argoCdClient the axios instance for the ArgoCD server
 * @returns {Promise<string|number>}
 */
async function refreshArgoCdToken(argoCdClient) {
  try {
    logger.debug('Retrieve token from the ArgoCD server');
    const data = JSON.stringify({username: process.env.ARGOCD_USERNAME, password: process.env.ARGOCD_PASSWORD})
    logger.debug(argoCdClient.defaults.baseURL);
    const resp = await argoCdClient.post('api/v1/session', data);
    const token = resp.data.token;

    logger.debug('Token: ' + token);
    if (!token) {
      logger.error('Failed to authenticate to ArgoCD due to undefined token');
      return Promise.reject();
    }

    return Promise.resolve(token)
  } catch (error) {
    logger.error('Failed to retrieve token from ArgoCD: ' + error)
    return Promise.reject(error);
  }
}

/**
 * List the existing applications in the ArgoCD server
 * @param argoCdClient the axios instance for the ArgoCD server
 * @returns {Promise<*>}
 */
async function listArgoCdAppNames(argoCdClient) {
  try {
    argoCdClient.defaults.headers['Authorization'] = 'Bearer ' + await refreshArgoCdToken(argoCdClient);
    logger.debug(`List applications from the ArgoCD server`);
    const resp = await argoCdClient.get('api/v1/applications');
    const apps = resp.data
    if (!apps) {
      logger.error('Failed to get ArgoCD applications, there is no response');
    }
    const appNames = resp.data.items.map(x => x.metadata.name)
    logger.debug(`Retrieved ArgoCD application names: ${JSON.stringify(appNames)}`);
    return appNames;
  } catch (error) {
    logger.error('Failed to get ArgoCD applications from the ArgoCD server: ' + error);
    return Promise.reject();
  }
}

/**
 * Returns a map of firestore docs groups by status
 * {"created": [], "deleted": []...}
 * @param firestore the firestore client
 * @returns {Promise<{}>}
 */
async function listFirestoreDocsByStatus(firestore) {
  logger.debug(`List Firestore documents by status in ${process.env.REGION} collection`);
  const collectionRef = firestore.collection(process.env.REGION);
  const documentRef = await collectionRef.listDocuments();
  const documentSnapshots = await firestore.getAll(...documentRef);
  let data = {
    'undefined': []
  };
  for (let documentSnapshot of documentSnapshots) {
    const collectionPath = path.join(documentSnapshot.ref.path, process.env.TENANT_CONTEXT_COLLECTION_NAME);
    logger.debug(`Discover path ${collectionPath}`);
    let subCollectionRef = await firestore.collection(collectionPath).get();
    await Promise.allSettled(subCollectionRef.docs.map(async doc => {
      if (!Array.isArray(data[doc.data().status])) {
        data[doc.data().status] = [];
      } else {
        if (doc.data().status) {
          data[doc.data().status] = [...data[doc.data().status], `${collectionPath}/${doc.id}`];
        } else {
          data['undefined'].push(`${collectionPath}/${doc.id}`)
        }
      }
    }));
  }
  logger.debug(`Firestore documents: ${JSON.stringify(data)}`);
  return data;
}

async function listEmptyFirestoreCollections(firestore) {
  logger.debug(`List empty firestore collections in ${process.env.REGION}`);
  const collectionRef = firestore.collection(process.env.REGION);
  const documentRef = await collectionRef.listDocuments();
  const documentSnapshots = await firestore.getAll(...documentRef);

  const emptyCollections = [];

  for (let documentSnapshot of documentSnapshots) {
    const collection = path.join(documentSnapshot.ref.path, process.env.TENANT_CONTEXT_COLLECTION_NAME);
    const snapshot = await firestore.collection(collection).count().get();
    if (snapshot.data().count === 0) {
      logger.debug(`The firestore collection ${collection} has no document`);
      emptyCollections.push(collection);
    }
  }

  return emptyCollections;
}

functions.http('cleanFirestore', async (req, res) => {
  requiredEnvironmentVariables([
    'ARGOCD_PASSWORD',
    'ARGOCD_URL',
    'ARGOCD_USERNAME',
    'GCP_FIRESTORE_PROJECT_ID',
    'GCP_PROJECT_ID',
    'LOG_LEVEL',
    'REGION',
    'TENANT_CONTEXT_COLLECTION_NAME',
  ])

  initializeLogger();
  logger.debug('Environment variables: ' + JSON.stringify(process.env));

  logger.debug(`Initialize axios instance for ArgoCD server at ${process.env.ARGOCD_URL}`);
  argoCdClient = axios.create({
    baseURL: process.env.ARGOCD_URL,
    httpsAgent: new https.Agent({
      // Trust self-signed certificates
      rejectUnauthorized: false
    }),
    headers: {
      'Content-Type': 'application/json'
    },
    timeout: 10000
  });

  logger.debug(`Initialize firestore client on ${process.env.GCP_FIRESTORE_PROJECT_ID} project`);
  const firestore = new Firestore({
    projectId: process.env.GCP_FIRESTORE_PROJECT_ID,
    timestampsInSnapshots: true
  })

  try {
    const existingArgoCdApps = await listArgoCdAppNames(argoCdClient);
    const firestoreDocs = await listFirestoreDocsByStatus(firestore);

    // Firestore docs to delete are present in firestore collection but have no associated ArgoCD apps
    const firestoreDocsToDelete = [
      ...firestoreDocs['created'],
      ...firestoreDocs['creation_failed'],
      ...firestoreDocs['deleted'],
      ...firestoreDocs['undefined']
    ].filter(x => !existingArgoCdApps.includes(x));

    logger.info('Detect following firestore documents that have no associated ArgoCD app: ' + JSON.stringify(firestoreDocsToDelete));

    await Promise.allSettled(firestoreDocsToDelete.map(async documentRef => {
      try {
        logger.info(`Delete ${documentRef} firestore document`);
        const collection = documentRef.split( '/' ).slice( 0, -1 ).join( '/' )
        const doc = documentRef.substring(documentRef.lastIndexOf('/') + 1)
        await firestore.collection(collection).doc(doc).delete();
      } catch (error) {
        logger.error(`Failed to delete ${documentRef} firestore document: ${error}`);
      }
    }));

    const emptyCollections = await listEmptyFirestoreCollections(firestore);
    for (let emptyCollection of emptyCollections) {
      const collectionPathArr = emptyCollection.split('/').slice(0, -1);
      const collectionToDelete = path.join(collectionPathArr[0], collectionPathArr[1]);
      try {
        logger.info(`Delete empty firestore collection ${collectionToDelete}`);
        await firestore.collection(collectionPathArr[0]).doc(collectionPathArr[1]).delete();
      } catch (error) {
        logger.error(`Failed to delete empty firestore collection ${collectionToDelete}: ${error}`);
      }
    }

    res.status(200).json({
      status_code: 200,
      message: `The firestore collection ${process.env.REGION} is cleaned`
    })
  } catch (error) {
    res.status(500).json({
      status_code: 500,
      message: error
    });
  }
});
