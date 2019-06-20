const timeout = 5000;

describe('Akeneoreferenceentity > infrastructure > remover > reference-entity', () => {
  let page = global.__PAGE__;

  beforeEach(async () => {
    await page.reload();
  }, timeout);

  it('It deletes a reference entity', async () => {
    page.on('request', interceptedRequest => {
      if (
        'http://pim.com/rest/reference_entity/designer' === interceptedRequest.url() &&
        'DELETE' === interceptedRequest.method()
      ) {
        interceptedRequest.respond({
          status: 204,
        });
      }
    });

    await page.evaluate(async () => {
      const createIdentifier = require('akeneoreferenceentity/domain/model/reference-entity/identifier')
        .createIdentifier;
      const remover = require('akeneoreferenceentity/infrastructure/remover/reference-entity').default;

      const identifierToDelete = createIdentifier('designer');

      return await remover.remove(identifierToDelete);
    });
  });
});
