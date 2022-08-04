/**
 * Responds to any HTTP request.
 *
 * @param {!express:Request} req HTTP request context.
 * @param {!express:Response} res HTTP response context.
 */

const axios = require('axios');
const querystring = require('querystring');
const {SecretManagerServiceClient} = require('@google-cloud/secret-manager');

const client = new SecretManagerServiceClient();

const secretName = process.env.SECRET_NAME;
const gcpProjectId = process.env.PROJECT_ID;
const portalHostname = process.env.PORTAL_HOSTNAME;
const portalLoginHostname = process.env.PORTAL_LOGIN_HOSTNAME;
const tenantFilter = {
  editionFlags: process.env.TENANT_EDITION_FLAGS,
  continent: process.env.TENANT_CONTINENT,
  environment: process.env.TENANT_ENVIRONMENT,
};

async function accessSecretVersion() {
  const [version] = await client.accessSecretVersion({
    name: `projects/${gcpProjectId}/secrets/${secretName}/versions/latest`
  });

  return version.payload.data.toString('utf-8');
}

async function getToken(credentials) {
  const url = `https://${portalLoginHostname}/auth/realms/connect/protocol/openid-connect/token`;
  const payload = querystring.stringify(JSON.parse(credentials));
  const resp = await axios.post(url, payload, {
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      'Content-Length': payload.length,
    }
  });
  const token = resp.data.access_token;
  if (token === undefined) {
    throw new Error('Error: access token is undefined');
  } else {
    return token;
  }
}

exports.getPortalInformation = (req, res) => {
  accessSecretVersion()
    .then((credentials) => {
      getToken(credentials)
        .then((token) => {
          const url = `https://${portalHostname}/api/v2/console/requests/pending_activation?subject_type=${tenantFilter.editionFlags}&continent=${tenantFilter.continent}&environment=${tenantFilter.environment}`;
          axios.get(url, {
            headers: {
              'Authorization': `Bearer ${token}`,
              'Content-Type': 'application/json',
            }
          })
            .then((resp) => {
              if (resp.status !== 200) {
                throw new Error(`Failed listing tenants from the portal: got HTTP status code ${resp.status} instead of HTTP 200`);
              } else if (typeof resp.data === undefined) {
                throw new Error('Failed listing tenants from the portal: returned HTTP data is undefined.');
              } else {
                console.log(resp.data);
              }
            })
            .catch((err) => {
              console.error(err);
              throw new Error('Failed to get the tenants from the portal');
            })
        }).catch((err) => {
        console.error(err);
        throw new Error('Failed to get a token from the portal');
      })
    }).catch((err) => {
      console.error(err);
      throw new Error('Failed to get credentials from SecretManager: ' + err);
  });
}
