const {getRequestContract, listenRequest} = require('../../../../acceptance/cucumber/tools');

const timeout = 5000;

describe('Akeneoassetfamily > infrastructure > remover > asset', () => {
  let page = global.__PAGE__;

  beforeEach(async () => {
    await page.reload();
  }, timeout);

  it('It deletes a asset', async () => {
    page.on('request', interceptedRequest => {
      if (
        'http://pim.com/rest/asset_manager/designer/asset/starck' === interceptedRequest.url() &&
        'DELETE' === interceptedRequest.method()
      ) {
        interceptedRequest.respond({
          status: 204,
        });
      }
    });

    await page.evaluate(async () => {
      const createAssetCode = require('akeneoassetmanager/domain/model/asset/code').createCode;
      const createAssetFamilyIdentifier = require('akeneoassetmanager/domain/model/asset-family/identifier')
        .createIdentifier;
      const remover = require('akeneoassetmanager/infrastructure/remover/asset').default;

      const assetCodeToDelete = createAssetCode('starck');
      const assetFamilyIdentifier = createAssetFamilyIdentifier('designer');

      return await remover.remove(assetFamilyIdentifier, assetCodeToDelete);
    });
  });

  it('It deletes all asset family assets', async () => {
    const requestContract = getRequestContract('Asset/DeleteAll/ok.json');
    await listenRequest(page, requestContract);

    await page.evaluate(async () => {
      const createAssetFamilyIdentifier = require('akeneoassetmanager/domain/model/asset-family/identifier')
        .createIdentifier;
      const remover = require('akeneoassetmanager/infrastructure/remover/asset').default;

      const assetFamilyIdentifier = createAssetFamilyIdentifier('designer');

      return await remover.removeAll(assetFamilyIdentifier);
    });
  });
});
