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
    debugger;
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

const answerLocaleList = async function() {
  const requestContract = getRequestContract('Locale/List/ok.json');

  await listenRequest(this.page, requestContract);
};

const answerChannelList = async function() {
  const requestContract = getRequestContract('Channel/List/ok.json');

  await listenRequest(this.page, requestContract);
};

const askForReferenceEntity = async function(identifier) {
  await answerLocaleList.apply(this);
  await answerChannelList.apply(this);
  await this.page.evaluate(async identifier => {
    const Controller = require('pim/controller/reference-entity/edit');
    const controller = new Controller();
    controller.renderRoute({params: {identifier, tab: 'attribute'}});

    await document.getElementById('app').appendChild(controller.el);
  }, identifier);

  await this.page.waitFor('.AknDefault-mainContent[data-tab="attribute"] .AknSubsection-container');
};

module.exports = {
  getRequestContract,
  listenRequest,
  askForReferenceEntity,
  answerLocaleList,
  answerChannelList,
};
