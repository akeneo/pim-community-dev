const timeout = 5000;

describe('Akeneoenrichedentity > infrastructure > remover > record', () => {
  let page = global.__PAGE__;

  beforeEach(async () => {
    await page.reload();
  }, timeout);

  it('It deletes a record', async () => {
    page.on('request', interceptedRequest => {
      if (
        'http://pim.com/rest/enriched_entity/designer/record/starck' === interceptedRequest.url() &&
        'DELETE' === interceptedRequest.method()
      ) {
        interceptedRequest.respond({
          status: 204,
        });
      }
    });

    await page.evaluate(async () => {
      const createRecordCode = require('akeneoenrichedentity/domain/model/record/code').createCode;
      const createEnrichedEntityIdentifier = require('akeneoenrichedentity/domain/model/enriched-entity/identifier')
        .createIdentifier;
      const remover = require('akeneoenrichedentity/infrastructure/remover/record').default;

      const recordCodeToDelete = createRecordCode('starck');
      const enrichedEntityIdentifier = createEnrichedEntityIdentifier('designer');

      return await remover.remove(enrichedEntityIdentifier, recordCodeToDelete);
    });
  });
});
