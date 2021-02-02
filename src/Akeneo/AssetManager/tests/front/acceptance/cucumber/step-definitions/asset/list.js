const path = require('path');
const Sidebar = require('../../decorators/asset-family/app/sidebar.decorator');
const Header = require('../../decorators/asset-family/app/header.decorator');
const Assets = require('../../decorators/asset-family/edit/assets.decorator');
const Modal = require('../../decorators/delete/modal.decorator');
const {getRequestContract, listenRequest, askForAssetFamily} = require('../../tools');
const LocaleSwitcher = require('../../decorators/asset-family/app/locale-switcher.decorator');
const ChannelSwitcher = require('../../decorators/asset-family/app/channel-switcher.decorator');

const {
  decorators: {createElementDecorator},
  tools: {answerJson},
} = require(path.resolve(process.cwd(), './tests/front/acceptance/cucumber/test-helpers.js'));

module.exports = async function(cucumber) {
  const {Given, When, Then} = cucumber;
  const assert = require('assert');
  let currentRequestContract;

  const config = {
    Sidebar: {
      selector: '.AknColumn',
      decorator: Sidebar,
    },
    Header: {
      selector: '.AknTitleContainer',
      decorator: Header,
    },
    Assets: {
      selector: '.AknDefault-mainContent',
      decorator: Assets,
    },
    Modal: {
      selector: '.modal',
      decorator: Modal,
    },
    LocaleSwitcher: {
      selector: '.locale-switcher',
      decorator: LocaleSwitcher,
    },
    ChannelSwitcher: {
      selector: '.channel-switcher',
      decorator: ChannelSwitcher,
    },
  };

  const getElement = createElementDecorator(config);

  const showAssetTab = async function(page) {
    const sidebar = await await getElement(page, 'Sidebar');
    await sidebar.clickOnTab('asset');
  };

  Then('the list of assets should be:', async function(expectedAssets) {
    await showAssetTab(this.page);

    const assetList = await await getElement(this.page, 'Assets');
    const isValid = await expectedAssets.hashes().reduce(async (isValid, expectedAsset) => {
      return (await isValid) && (await assetList.hasAsset(expectedAsset.identifier));
    }, true);
    assert.strictEqual(isValid, true);
  });

  Then('the list of assets should be empty', async function() {
    await showAssetTab(this.page);

    const assets = await await getElement(this.page, 'Assets');
    const isEmpty = await assets.isEmpty();

    assert.strictEqual(isEmpty, true);
  });

  Then('the list of assets should not be empty', async function() {
    await showAssetTab(this.page);

    const assets = await await getElement(this.page, 'Assets');
    const isEmpty = await assets.isEmpty();

    assert.strictEqual(isEmpty, false);
  });

  Given('the following assets for the asset family {string}:', async function(assetFamilyIdentifier, assets) {
    const assetsSaved = assets.hashes().map(normalizedAsset => {
      return {
        identifier: normalizedAsset.identifier,
        asset_family_identifier: assetFamilyIdentifier,
        code: normalizedAsset.code,
        labels: JSON.parse(normalizedAsset.labels),
      };
    });
    this.page.on('request', request => {
      if (
        `http://pim.com/rest/asset_manager/${assetFamilyIdentifier}/asset` === request.url() &&
        'GET' === request.method()
      ) {
        answerJson(request, {items: assetsSaved, matches_count: assetsSaved.length});
      }
    });
  });

  Then('the user should see the successfull deletion notification', async function() {
    const assetsPage = await await getElement(this.page, 'Assets');
    const hasSuccessNotification = await assetsPage.hasSuccessNotification();

    assert.strictEqual(hasSuccessNotification, true);
  });

  Then('the user should see the failed deletion notification', async function() {
    const assetsPage = await await getElement(this.page, 'Assets');
    const hasSuccessNotification = await assetsPage.hasErrorNotification();

    assert.strictEqual(hasSuccessNotification, true);
  });

  Then('the user should not see the delete all button', async function() {
    await showAssetTab(this.page);

    const header = await await getElement(this.page, 'Header');
    const isDeleteButtonVisible = await header.isDeleteButtonVisible();

    assert.strictEqual(isDeleteButtonVisible, false);
  });

  Then('the list of assets should be:', async function(expectedAssets) {
    await showAssetTab(this.page);

    const assetList = await await getElement(this.page, 'Assets');
    const isValid = await expectedAssets.hashes().reduce(async (isValid, expectedAsset) => {
      return (await isValid) && (await assetList.hasAsset(expectedAsset.identifier));
    }, true);
    assert.strictEqual(isValid, true);
  });

  Given('the user asks for a list of assets', async function() {
    const requestContract = getRequestContract('AssetFamily/AssetFamilyDetails/ok.json');
    await listenRequest(this.page, requestContract);
    const assetsRequestContract = getRequestContract('Asset/Search/not_filtered.json');
    await listenRequest(this.page, assetsRequestContract);

    await askForAssetFamily.apply(this, ['designer']);
    await showAssetTab(this.page);
  });

  Given('the user asks for a list of assets having different completenesses', async function() {
    const requestContract = getRequestContract('AssetFamily/AssetFamilyDetails/ok.json');
    await listenRequest(this.page, requestContract);
    currentRequestContract = getRequestContract('Asset/Search/not_filtered.json');
    await listenRequest(this.page, currentRequestContract);

    await askForAssetFamily.apply(this, ['designer']);
    await showAssetTab(this.page);
  });

  Then('the user should see that {string} is complete at {int}%', async function(assetCode, completeLevel) {
    const assetList = await await getElement(this.page, 'Assets');

    const starckAsset = currentRequestContract.response.body.items.find(item => item.code === assetCode);
    const completeness = await assetList.getAssetCompleteness(starckAsset.identifier);

    assert.strictEqual(completeness, completeLevel);
  });

  When('the user searches for {string}', async function(searchInput) {
    const requestContract = getRequestContract(
      's' === searchInput ? 'Asset/Search/ok.json' : 'Asset/Search/no_result.json'
    );

    await listenRequest(this.page, requestContract);

    const assetList = await await getElement(this.page, 'Assets');
    await assetList.search(searchInput);
  });

  When('the user searches for assets with red color', async function() {
    const requestContract = getRequestContract('Asset/Search/color_filtered.json');

    await listenRequest(this.page, requestContract);

    const assetList = await await getElement(this.page, 'Assets');
    await assetList.filterOption('colors', ['red']);
  });

  When('the user searches for assets with linked to paris', async function() {
    const requestContract = getRequestContract('Asset/Search/city_filtered.json');

    await listenRequest(this.page, requestContract);

    const assetList = await await getElement(this.page, 'Assets');
    await assetList.filterLink('city', 'paris');
  });

  When('the user filters on the complete assets', async function() {
    const requestContract = getRequestContract('Asset/Search/complete_filtered.json');

    await listenRequest(this.page, requestContract);

    const assetList = await await getElement(this.page, 'Assets');
    await assetList.completeFilter('yes');
  });

  When('the user filters on the uncomplete assets', async function() {
    const requestContract = getRequestContract('Asset/Search/uncomplete_filtered.json');

    await listenRequest(this.page, requestContract);

    const assetList = await await getElement(this.page, 'Assets');
    await assetList.completeFilter('no');
  });

  Then('the user should see a filtered list of assets', async function() {
    const assetList = await await getElement(this.page, 'Assets');
    const isValid = await [
      'designer_dyson_01afdc3e-3ecf-4a86-85ef-e81b2d6e95fd',
      'designer_starck_29aea250-bc94-49b2-8259-bbc116410eb2',
    ].reduce(async (isValid, expectedAsset) => {
      return (await isValid) && (await assetList.hasAsset(expectedAsset));
    }, true);
    assert.strictEqual(isValid, true);
  });

  Then('the user should see a filtered list of red assets', async function() {
    const assetList = await await getElement(this.page, 'Assets');
    const isValid = await [
      'designer_dyson_01afdc3e-3ecf-4a86-85ef-e81b2d6e95fd',
      'designer_starck_29aea250-bc94-49b2-8259-bbc116410eb2',
    ].reduce(async (isValid, expectedAsset) => {
      return (await isValid) && (await assetList.hasAsset(expectedAsset));
    }, true);
    assert.strictEqual(isValid, true);
  });

  Then('the user should see a filtered list of assets linked to paris', async function() {
    const assetList = await await getElement(this.page, 'Assets');
    const isValid = await [
      'designer_dyson_01afdc3e-3ecf-4a86-85ef-e81b2d6e95fd',
      'designer_starck_29aea250-bc94-49b2-8259-bbc116410eb2',
    ].reduce(async (isValid, expectedAsset) => {
      return (await isValid) && (await assetList.hasAsset(expectedAsset));
    }, true);
    assert.strictEqual(isValid, true);
  });

  Then('I switch to another locale in the asset grid', async function() {
    const assetList = await await getElement(this.page, 'Assets');
    await assetList.search('other_s');

    const requestContract = getRequestContract('Asset/Search/no_result_fr.json');

    await listenRequest(this.page, requestContract);
    await (await await getElement(this.page, 'ChannelSwitcher')).switchChannel('mobile');
    await (await await getElement(this.page, 'LocaleSwitcher')).switchLocale('fr_FR');
  });

  Then('the user should see an unfiltered list of assets', async function() {
    const assetList = await await getElement(this.page, 'Assets');
    const expectedAssetIdentifiers = [
      'designer_dyson_01afdc3e-3ecf-4a86-85ef-e81b2d6e95fd',
      'designer_starck_29aea250-bc94-49b2-8259-bbc116410eb2',
      'designer_coco_34aee120-fa95-4ff2-8439-bea116120e34',
    ];

    for (const expectedAssetIdentifier of expectedAssetIdentifiers) {
      await assetList.hasAsset(expectedAssetIdentifier);
    }
  });

  Then('the user should see a list of complete assets', async function() {
    const assetList = await await getElement(this.page, 'Assets');

    const expectedAssetIdentifiers = ['brand_coco_0134dc3e-3def-4afr-85ef-e81b2d6e95fd'];

    for (const expectedAssetIdentifier of expectedAssetIdentifiers) {
      const isValid = await assetList.hasAsset(expectedAssetIdentifier);

      assert.strictEqual(isValid, true);
    }
  });

  Then('the user should see a list of uncomplete assets', async function() {
    const assetList = await await getElement(this.page, 'Assets');
    const expectedAssetIdentifiers = ['designer_dyson_01afdc3e-3ecf-4a86-85ef-e81b2d6e95fd'];

    for (const expectedAssetIdentifier of expectedAssetIdentifiers) {
      await assetList.hasAsset(expectedAssetIdentifier);
    }
  });
};
