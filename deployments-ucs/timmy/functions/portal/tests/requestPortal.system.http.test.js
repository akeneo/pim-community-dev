const assert = require('assert');
const {request} = require('gaxios');
const childProcess = require('child_process');
const fs = require('fs');
const path = require("path");

const mockData = require('../../../../k8s/infra/charts/wiremock/responses/sandbox_europe_serenity_pending_creation.json');

const FUNCTION_URL = process.env.FUNCTION_URL || 'http://localhost:8083';

describe('Test the returned HTTP status of the function', () => {
  it('requestTenants: should return http status code 200 and expected data', async() => {
    const response = await request({
      url: FUNCTION_URL,
      method: 'GET'
    });
    assert.strictEqual(response.status, 200);
    assert.deepStrictEqual(response.data, mockData);
  })
});
