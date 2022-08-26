const assert = require('assert');
const {request} = require('gaxios');
const childProcess = require('child_process');


if (!process.env.MAILER_API_KEY) {
  throw new Error('"MAILER_API_KEY" env var must be set.');
}

const INSTANCE_NAME = process.env.INSTANCE_NAME || 'test';
const FUNCTION_URL = process.env.FUNCTION_URL || 'http://localhost:8080';

describe('Test to create a new tenant', () => {
  it('createTenant: should create the new tenant', async() => {
    const instanceName = INSTANCE_NAME;
    const response = await request({
      url: FUNCTION_URL,
      method: 'POST',
      data: {
        instanceName: instanceName,
        mailer: {
          login: "test@mg.cloud.akeneo.com",
          password: Math.random().toString(36).slice(-8),
          base_mailer_url: "smtp://smtp.mailgun.org:2525",
          domain: "mg.cloud.akeneo.com",
          api_key: process.env.MAILER_API_KEY
        },
        pim: {
          defaultAdminUser: {
            login: 'pim-admin',
            firstName: 'John',
            lastName: 'Doe',
            email: `pim-admin-${instanceName}@akeneo.fr`,
            password: Math.random().toString(36).slice(-8),
            uiLocale: 'en_US'
          },
          monitoring: {
            authenticationToken: Math.random().toString(36).slice(-8)
          },
          secret: Math.random().toString(36).slice(-8)
        }
      }
    });
    assert.strictEqual(response.data, `The new tenant ${instanceName} is successfully created!`)
  })
});



