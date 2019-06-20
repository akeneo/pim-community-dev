const timeout = 5000;

describe('Akeneoassetfamily > infrastructure > remover > asset-family', () => {
  let page = global.__PAGE__;

  beforeEach(async () => {
    await page.reload();
  }, timeout);

  it('It deletes an asset family', async () => {
    page.on('request', interceptedRequest => {
      if (
        'http://pim.com/rest/asset_manager/designer' === interceptedRequest.url() &&
        'DELETE' === interceptedRequest.method()
      ) {
        interceptedRequest.respond({
          status: 204,
        });
      }
    });

    await page.evaluate(async () => {
      const createIdentifier = require('akeneoassetmanager/domain/model/asset-family/identifier')
        .createIdentifier;
      const remover = require('akeneoassetmanager/infrastructure/remover/asset-family').default;

      const identifierToDelete = createIdentifier('designer');

      return await remover.remove(identifierToDelete);
    });
  });
});
