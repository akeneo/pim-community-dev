const assert = require('assert');
const {request} = require('gaxios');
const childProcess = require('child_process');
const fs = require('fs');
const path = require("path");


const FUNCTION_URL = process.env.FUNCTION_URL || 'http://localhost:8081';

describe('Test the returned HTTP status of the function', () => {
  it('requestTenants: should return http status code 200', async() => {
    const response = await request({
      url: FUNCTION_URL,
      method: 'GET'
    });
    assert.strictEqual(response.status, 200);
  })
});
