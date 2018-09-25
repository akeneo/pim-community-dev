const Edit = require('../../decorators/record/edit.decorator');
const Header = require('../../decorators/enriched-entity/app/header.decorator');
const path = require('path');
const fs = require('fs');

const {
  decorators: {createElementDecorator},
  tools: {convertItemTable, answerJson},
} = require(path.resolve(process.cwd(), './tests/front/acceptance/cucumber/test-helpers.js'));

let currentRequestContract = {};

module.exports = async function(cucumber) {
  const listenRequest = async function(page, fileName) {
    const requestContract = JSON.parse(
      fs.readFileSync(process.cwd() + '/src/Akeneo/EnrichedEntity/tests/shared/responses/Record/' + fileName, 'utf-8')
    );
    currentRequestContract = requestContract;

    const url = await page.evaluate(
      async (route, query) => {
        const router = require('pim/router');

        return router.generate(route, query);
      },
      requestContract.request.route,
      requestContract.request.query
    );

    const answerRequest = request => {
      if (url === request.url() && requestContract.request.method === request.method()) {
        answerJson(request, requestContract.response.body, requestContract.response.status);
        page.removeListener('request', answerRequest);
      }
    };
    page.on('request', answerRequest);
  };
  const {When, Then, Given} = cucumber;
  const assert = require('assert');

  const config = {
    Header: {
      selector: '.AknTitleContainer',
      decorator: Header,
    },
    Edit: {
      selector: '.AknDefault-contentWithColumn',
      decorator: Edit,
    },
  };

  const getElement = createElementDecorator(config);

  Given('a valid record', async function() {
    return await listenRequest(this.page, 'RecordDetails/ok.json');
  });
  Given('an invalid record', async function() {
    return await listenRequest(this.page, 'RecordDetails/not_found.json');
  });

  const askForRecord = async function(recordCode, enrichedEntityIdentifier) {
    await this.page.evaluate(
      async (enrichedEntityIdentifier, recordCode) => {
        const Controller = require('pim/controller/record/edit');
        const controller = new Controller();
        controller.renderRoute({params: {enrichedEntityIdentifier, recordCode, tab: 'enrich'}});
        await document.getElementById('app').appendChild(controller.el);
      },
      enrichedEntityIdentifier,
      recordCode
    );
    await this.page.waitFor('.AknDefault-mainContent[data-tab="enrich"] .content');
    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    const isLoaded = await enrich.isLoaded();
    assert.strictEqual(isLoaded, true);
  };

  const answerLocaleList = function() {
    this.page.on('request', request => {
      if ('http://pim.com/configuration/locale/rest?activated=true' === request.url() && 'GET' === request.method()) {
        answerJson(
          request,
          [
            {code: 'de_DE', label: 'German (Germany)', region: 'Germany', language: 'German'},
            {code: 'en_US', label: 'English (United States)', region: 'United States', language: 'English'},
            {code: 'fr_FR', label: 'French (France)', region: 'France', language: 'French'},
          ],
          200
        );
      }
    });
  };

  When('the user ask for the record', async function() {
    await answerLocaleList.apply(this);
    await askForRecord.apply(this, [
      currentRequestContract.request.query.recordCode,
      currentRequestContract.request.query.enrichedEntityIdentifier,
    ]);
  });

  Then('the record should be:', async function(updates) {
    const record = convertItemTable(updates)[0];
    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    for (let locale in record.labels) {
      const label = record.labels[locale];
      await (await editPage.getLocaleSwitcher()).switchLocale(locale);
      const labelValue = await await enrich.getLabel();
      assert.strictEqual(labelValue, label);
    }
  });
};
