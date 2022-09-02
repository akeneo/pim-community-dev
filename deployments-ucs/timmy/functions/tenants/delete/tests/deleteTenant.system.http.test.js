const assert = require('assert');
const {request} = require('gaxios');

const INSTANCE_NAME = process.env.INSTANCE_NAME || 'test';
const FUNCTION_URL = process.env.FUNCTION_URL || 'http://localhost:8081';

describe('Test to delete a tenant', () => {
  it('deleteTenant: should delete the tenant', async() => {
    console.log(FUNCTION_URL);
    const response = await request({
      url: `${FUNCTION_URL}/${INSTANCE_NAME}`,
      method: 'DELETE',
      data: {}
    });
    assert.strictEqual(response.data, `The tenant ${INSTANCE_NAME} is deleted!`)
  });
});
