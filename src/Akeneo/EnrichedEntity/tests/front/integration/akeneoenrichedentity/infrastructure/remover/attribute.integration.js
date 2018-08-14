const timeout = 5000;

describe('Akeneoenrichedentity > infrastructure > remover > attribute', () => {
  let page = global.__PAGE__;

  beforeEach(async () => {
    await page.reload();
  }, timeout);

  it('It deletes an attribute', async () => {
    page.on('request', interceptedRequest => {
      if (
        'http://pim.com/rest/enriched_entity/designer/attribute/name' === interceptedRequest.url() &&
        'DELETE' === interceptedRequest.method()
      ) {
        interceptedRequest.respond({
          status: 204
        });
      }
    });

    await page.evaluate(async () => {
      const createIdentifier = require('akeneoenrichedentity/domain/model/attribute/identifier').createIdentifier;
      const remover = require('akeneoenrichedentity/infrastructure/remover/attribute').default;

      const identifierToDelete = createIdentifier('designer', 'name');

      return await remover.remove(identifierToDelete);
    });
  });
});
