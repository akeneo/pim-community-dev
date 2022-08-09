/**
 * Responds to any HTTP request.
 *
 * @param {!express:Request} req HTTP request context.
 * @param {!express:Response} res HTTP response context.
 */

const axios = require('axios');
const querystring = require('querystring');

exports.getPortalInformation = (req, res) => {
  const portalHostname = process.env.PORTAL_HOSTNAME;
  const portalLoginHostname = process.env.PORTAL_LOGIN_HOSTNAME;
  const tenantFilter = {
    editionFlags: process.env.TENANT_EDITION_FLAGS,
    continent: process.env.TENANT_CONTINENT,
    environment: process.env.TENANT_ENVIRONMENT,
  };

  // The credentials are stored in SecretManager and mounted as a envvar.
  const portalCredentials = process.env.PORTAL_CREDENTIALS;

  const getToken = async () => {
    try {
      let url = `https://${portalLoginHostname}/auth/realms/connect/protocol/openid-connect/token`;
      const payload = querystring.stringify(JSON.parse(portalCredentials));

      const resp = await axios.post(url, payload, {
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'Content-Length': payload.length,
        }
      });

      if (resp.status !== 200) {
        throw new Error(`Authentication failed: expects HTTP 200 status code, got ${resp.status}: ${resp.statusText}`);
      } else if (typeof resp.data === undefined) {
        throw new Error('Authentication failed: returned HTTP data is undefined');
      } else {
        return resp.data.access_token;
      }

    } catch(err) {
      console.error(err);
      process.exit(1);
    }
  }


  getToken().then((token) => {
    url = `https://${portalHostname}/api/v2/console/requests/pending_activation?subject_type=${tenantFilter.editionFlags}&continent=${tenantFilter.continent}&environment=${tenantFilter.environment}`;
    const resp = axios.get(url, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    }).then((resp) => {
      if (resp.status !== 200) {
        throw new Error(`Failed listing tenants from the portal: got HTTP status code ${resp.status} instead of HTTP 200`);
      } else if (typeof resp.data === undefined) {
        throw new Error('Failed listing tenants from the portal: returned HTTP data is undefined.');
      } else {
        console.log(resp.data);
      }
    }).catch((err) => {
      console.error(err);
      process.exit(2);
    });
  }).catch((err) => {
    console.log(err);
  });
};
