const index = require('../index.js');
const assert = require('assert');

describe('Ensure required environment variables are not missing', () => {
  it('All environment variables are defined', () => {
    process.env['ENV1'] = '1';
    process.env['ENV2'] = '2';

    try {
      index.requiredEnvironmentVariables(['ENV1', 'ENV2']);
      assert.ok(true);
    } catch (err) {
      assert.fail(err);
    }
  });

  it('No required environment variable is defined', () => {
    try {
      index.requiredEnvironmentVariables(['ENV1', 'ENV2']);
      assert.fail();
    } catch {
      assert.ok(true);
    }
  });

  it('Not all required environment variables are defined', () => {
    process.env['ENV1'] = '1';

    try {
      index.requiredEnvironmentVariables(['ENV1', 'ENV2']);
      assert.fail();
    } catch {
      assert.ok(true);
    }
  });
});

describe('Ensure we can prefix url with the git branch name', () => {
  it('There is no prefix in url when branch name is master', () => {
    assert.equal(index.prefixUrlWithBranchName('https://portal.akeneo.com', 'master'), 'https://portal.akeneo.com');
    assert.notEqual(index.prefixUrlWithBranchName('https://portal.akeneo.com', 'master'), 'https://portal.akeneo.com/master');
  });

  it('The url is prefixed with the branch name when it is not master', () => {
    assert.notEqual(index.prefixUrlWithBranchName('https://portal.akeneo.com', 'ph-123'), 'https://portal.akeneo.com');
    assert.equal(index.prefixUrlWithBranchName('https://portal.akeneo.com', 'ph-123'), 'https://portal.akeneo.com/ph-123/');
  });
});
