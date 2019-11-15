module.exports = async function(cucumber) {
  const {Given, When} = cucumber;
  const {
    decorators: {createElementDecorator},
  } = require('../test-helpers.js');

  const config = {
    'Asset picker': {
      selector: 'div[data-container="asset-picker"]',
      decorator: require('../decorator/asset-picker/asset-picker'),
    },
    'Designer asset collection': {
      selector: 'div[data-attribute="designer"]',
      decorator: require('../decorator/asset-collection'),
    },
  };
  const getElement = createElementDecorator(config);

  Given('the user opens the asset picker', async function() {
    const assetCollection = await getElement(this.page, 'Designer asset collection');
    await assetCollection.openPicker();
  });

  When('the user filters the assets', async function() {
    const assetPicker = await getElement(this.page, 'Asset picker');
    const searchBar = await assetPicker.getSearchBar();
    await searchBar.search('s');

    const filterCollection = await assetPicker.getFilterCollection();
    filterCollection.filter('colors', 'red');
  });

  When('the user picks one asset', async function() {
    const assetPicker = await getElement(this.page, 'Asset picker');
    const mosaic = await assetPicker.getMosaic();
    await mosaic.select('dyson');

    const basket = await assetPicker.getBasket();
    await basket.containsAsset('dyson');

    await assetPicker.confirmSelection();
  });
};
