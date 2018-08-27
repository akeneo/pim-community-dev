const timeout = 5000;

describe('Akeneoenrichedentity > infrastructure > remover > enriched-entity', () => {
  let page = global.__PAGE__;

  beforeEach(async () => {
    await page.reload();
  }, timeout);

  it('It deletes an enriched entity', async () => {
    page.on('request', interceptedRequest => {
      if (
        'http://pim.com/rest/enriched_entity/designer' === interceptedRequest.url() &&
        'DELETE' === interceptedRequest.method()
      ) {
        interceptedRequest.respond({
          status: 204
        });
      }
    });

    await page.evaluate(async () => {
      const createIdentifier = require('akeneoenrichedentity/domain/model/enriched-entity/identifier').createIdentifier;
      const remover = require('akeneoenrichedentity/infrastructure/remover/enriched-entity').default;

      const identifierToDelete = createIdentifier('designer');

      return await remover.remove(identifierToDelete);
    });
  });
});
