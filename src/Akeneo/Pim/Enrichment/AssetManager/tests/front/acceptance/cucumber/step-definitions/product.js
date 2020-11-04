module.exports = async function(cucumber) {
  const {Given, Then, When} = cucumber;
  const assert = require('assert');
  const {
    decorators: {createElementDecorator},
    tools: {renderView},
  } = require('../test-helpers.js');

  const {
    answerChannelList,
    answerRuleRelationList,
    answerAttributeGroup,
    answerProductAttributeList,
    answerPermissionList,
    answerAssetFamilyDetails,
    answerAssetList,
    answerAssetAttributes,
  } = require('../helpers/fetchers');

  const {grantAllAcls} = require('../helpers/acl.js');

  const config = {
    'Designer asset collection': {
      selector: 'div[data-attribute="designer"]',
      decorator: require('../decorator/asset-collection'),
    },
    'Asset preview': {
      selector: 'div[data-role="asset-preview-modal"]',
      decorator: require('../decorator/asset-preview'),
    },
  };

  const product = {
    meta: {level: null},
    family: 'accessories',
    categories: [],
    values: {},
  };

  const getElement = createElementDecorator(config);
  const getAssetCollection = async page => {
    return await getElement(page, 'Designer asset collection');
  };

  const assertAssetCodesToBe = async (page, expectedAssetCodes) => {
    const assetCollection = await getAssetCollection(page);
    if (expectedAssetCodes.length === 0) {
      const collectionIsEmpty = await assetCollection.isEmpty();
      assert.equal(collectionIsEmpty, true);

      return;
    }

    const assetCodes = await assetCollection.getAssetCodes();
    assert.deepEqual(assetCodes, expectedAssetCodes);
  };

  Given('an asset collection with three assets', async function() {
    answerChannelList(this.page);
    answerRuleRelationList(this.page);
    answerAttributeGroup(this.page);
    answerProductAttributeList(this.page);
    answerPermissionList(this.page);
    answerAssetFamilyDetails(this.page);
    answerAssetList(this.page);
    grantAllAcls(this.page);
    answerAssetAttributes(this.page);

    product.values.designer = [
      {
        locale: null,
        scope: null,
        data: ['starck', 'coco', 'dyson'],
      },
    ];
  });

  Given('an asset collection with two assets', async function() {
    answerChannelList(this.page);
    answerRuleRelationList(this.page);
    answerAttributeGroup(this.page);
    answerProductAttributeList(this.page);
    answerPermissionList(this.page);
    answerAssetFamilyDetails(this.page);
    answerAssetList(this.page);
    grantAllAcls(this.page);
    answerAssetAttributes(this.page);

    product.values.designer = [
      {
        locale: null,
        scope: null,
        data: ['starck', 'coco'],
      },
    ];
  });

  When('the user go to the asset tab', async function() {
    await renderView(this.page, 'pim-product-edit-form-assets', product);
  });

  When('remove an asset', async function() {
    const assetCollection = await getAssetCollection(this.page);
    const coco = await assetCollection.getAsset('coco');

    await coco.remove();
  });

  When('remove all assets', async function() {
    const assetCollection = await getAssetCollection(this.page);

    await assetCollection.removeAll();
  });

  When('move an asset', async function() {
    const assetCollection = await getAssetCollection(this.page);
    const coco = await assetCollection.getAsset('coco');

    await coco.move('right');
  });

  When('the user open the first asset preview', async function() {
    const assetCollection = await getAssetCollection(this.page);
    const starck = await assetCollection.getAsset('starck');

    await starck.preview();
  });

  Then('the preview should be displayed', async function() {
    await getElement(this.page, 'Asset preview');
  });

  Then('the first asset should be displayed', async function() {
    const assetPreview = await getElement(this.page, 'Asset preview');
    const image = await assetPreview.getImagePreviewed();

    assert.strictEqual(await image.getCode(), 'Starck');
  });

  Then('the three assets in the collection should be displayed', async function() {
    await assertAssetCodesToBe(this.page, ['starck', 'coco', 'dyson']);
  });

  Then('I should only see two remaining assets', async function() {
    await assertAssetCodesToBe(this.page, ['starck', 'dyson']);
  });

  Then('I should only see the reordered assets', async function() {
    await assertAssetCodesToBe(this.page, ['starck', 'dyson', 'coco']);
  });

  Then('there should be no asset in the collection', async function() {
    await assertAssetCodesToBe(this.page, []);
  });
};
