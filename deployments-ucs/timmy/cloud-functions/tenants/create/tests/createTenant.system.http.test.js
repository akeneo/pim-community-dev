const assert = require('assert');
const {request} = require('gaxios');
const childProcess = require('child_process');


if (!process.env.MAILER_API_KEY) {
  throw new Error('"MAILER_API_KEY" env var must be set.');
}

const TENANT_NAME = process.env.TENANT_NAME || 'test';
const FUNCTION_URL = process.env.FUNCTION_URL || 'http://localhost:8082';

describe('Test to create a new tenant', () => {
  it('createTenant: should create the new tenant', async() => {
    const tenant_name = TENANT_NAME;
    const response = await request({
      url: FUNCTION_URL,
      method: 'POST',
      data: {
        tenant_name: tenant_name,
        pim: {
          defaultAdminUser: {
            login: 'pim-admin',
            firstName: 'John',
            lastName: 'Doe',
            email: `pim-admin-${tenant_name}@akeneo.fr`,
            uiLocale: 'en_US'
          },
        }
      }
    });
    assert.strictEqual(response.data, `The new tenant ${tenant_name} is successfully created!`)
  })
});
