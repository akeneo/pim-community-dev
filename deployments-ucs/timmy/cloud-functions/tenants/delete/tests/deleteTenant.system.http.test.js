const assert = require('assert');
const {request} = require('gaxios');

const TENANT_NAME = process.env.TENANT_NAME || 'test';
const FUNCTION_URL = process.env.FUNCTION_URL || 'http://localhost:8083';

describe('Test to delete a tenant', () => {
  it('deleteTenant: should delete the tenant', async() => {
    console.log(FUNCTION_URL);
    const response = await request({
      url: `${FUNCTION_URL}/${TENANT_NAME}`,
      method: 'DELETE',
      data: {}
    });
    assert.strictEqual(response.data, `The tenant ${TENANT_NAME} is deleted!`)
  });
});
