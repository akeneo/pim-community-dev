const fs = require('fs');
const path = require('path');

const {
  tools: {answerJson},
} = require(path.resolve(process.cwd(), './tests/front/acceptance/cucumber/test-helpers.js'));

const getRequestContract = fileName => {
  return JSON.parse(
    fs.readFileSync(`${process.cwd()}/src/Akeneo/ReferenceEntity/tests/shared/responses/${fileName}`, 'utf-8')
  );
};

const listenRequest = async function(page, requestContract) {
  const url = await page.evaluate(
    async (route, query) => {
      const router = require('pim/router');

      return router.generate(route, query);
    },
    requestContract.request.route,
    requestContract.request.query
  );

  const answerRequest = request => {
    if (
      url === request.url() &&
      requestContract.request.method === request.method() &&
      JSON.stringify(requestContract.request.body) === request.postData()
    ) {
      answerJson(request, requestContract.response.body, requestContract.response.status);
      page.removeListener('request', answerRequest);
    }
  };
  page.on('request', answerRequest);
};

module.exports = {
  getRequestContract,
  listenRequest,
};
