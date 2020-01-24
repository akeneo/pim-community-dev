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
      // Sometimes this test fails on circle ci. This wait should mitigate that
      await new Promise((resolve) => setTimeout(resolve, 500));

      const remover = require('akeneoassetmanager/infrastructure/remover/asset-family').default;

      return await remover.remove('designer');
    });
  });
});
