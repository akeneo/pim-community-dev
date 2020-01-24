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
      // Sometimes this test fails on circle ci. This wait should mitigate that
      await new Promise((resolve) => setTimeout(resolve, 500));

      const remover = require('akeneoassetmanager/infrastructure/remover/attribute').default;

      return await remover.remove('designer', 'name_1234');
    });
  });
});
