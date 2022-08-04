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

async function getToken(res) {
  try {
    // Crossplane or config connector does not give the possibility to mount a GoogleSecret as environment variable
    // As a workaround we retrieve it here.
    const [version] = await client.accessSecretVersion({
      name: `projects/${gcpProjectId}/secrets/${secretName}/versions/latest`
    });

    const credentials = version.payload.data.toString('utf-8');
    const url = `https://${portalLoginHostname}/auth/realms/connect/protocol/openid-connect/token`;
    const payload = querystring.stringify(JSON.parse(credentials));
    const resp = await axios.post(url, payload, {
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'Content-Length': payload.length,
      }
    });
    const token = await resp.data.access_token;
    if (token === undefined) {
      res.status(500).send('The access token is undefined');
    } else {
      return token;
    }
  } catch(err) {
    console.error(err);
    res.status(500).send('Failed to get access token from the portal');
  }
}

exports.requestPortal = (req, res) => {
  const getTenants = async () => {
    const token = await getToken(res);
    const url = `https://${portalHostname}/api/v2/console/requests/pending_activation?subject_type=${tenantFilter.editionFlags}&continent=${tenantFilter.continent}&environment=${tenantFilter.environment}`;
    return await axios.get(url, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
      }
    })
  }

  getTenants()
    .then((resp) => {
      console.log(resp);
      if (resp.status !== 200) {
        throw new Error(`Failed listing tenants from the portal: got HTTP status code ${resp.status} instead of HTTP 200`);
      } else if (typeof resp.data === undefined) {
        throw new Error('Failed listing tenants from the portal: returned HTTP data is undefined.');
      } else {
        res.status(200).json(resp.data)
      }
    })
    .catch((err) => {
      console.error(err);
      res.status(500).send('Failed to get the tenants from the portal');
    })
}
