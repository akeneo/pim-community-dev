const path = require('path');

const {
  tools: {answerJson, convertDataTable}
} = require(path.resolve(
  process.cwd(),
  './tests/front/acceptance/cucumber/test-helpers.js'
));

module.exports = async function(cucumber) {
  const {Given} = cucumber;

  const listAcls = function (page, acls) {
    page.on('request', request => {
      if ('http://pim.com/rest/security/' === request.url()) {
        answerJson(request, acls, 200);
      }
    })
  };

  Given('the user has the following rights:', {timeout: 50000},  async function (aclsTable) {
    const acls = convertDataTable(aclsTable);
    listAcls(this.page, acls);
    await this.page.evaluate(() => {
      const securityContext = require('pim/security-context');

      return new Promise((resolve) => securityContext.initialize().then(resolve));
    });
  });

  Given('the user doesn\'t have any rights',  async function () {
    listAcls(this.page, []);
  });
};
