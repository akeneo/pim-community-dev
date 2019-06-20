const timeout = 5000;

describe('Akeneoassetfamily > infrastructure > remover > attribute', () => {
  let page = global.__PAGE__;

  beforeEach(async () => {
    await page.reload();
  }, timeout);

  it('It deletes an attribute', async () => {
    page.on('request', interceptedRequest => {
      if (
        'http://pim.com/rest/asset_manager/designer/attribute/name_1234' === interceptedRequest.url() &&
        'DELETE' === interceptedRequest.method()
      ) {
        interceptedRequest.respond({
          status: 204,
        });
      }
    });

    await page.evaluate(async () => {
      const createIdentifier = require('akeneoassetmanager/domain/model/attribute/identifier').createIdentifier;
      const createAssetFamilyIdentifier = require('akeneoassetmanager/domain/model/asset-family/identifier')
        .createIdentifier;
      const remover = require('akeneoassetmanager/infrastructure/remover/attribute').default;

      const attributeIdentifierToDelete = createIdentifier('name_1234');
      const assetFamilyIdentifierToDelete = createAssetFamilyIdentifier('designer');

      return await remover.remove(assetFamilyIdentifierToDelete, attributeIdentifierToDelete);
    });
  });
});
