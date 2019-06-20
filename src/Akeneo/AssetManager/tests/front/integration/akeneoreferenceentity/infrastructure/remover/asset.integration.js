const {getRequestContract, listenRequest} = require('../../../../acceptance/cucumber/tools');

const timeout = 5000;

describe('Akeneoreferenceentity > infrastructure > remover > record', () => {
  let page = global.__PAGE__;

  beforeEach(async () => {
    await page.reload();
  }, timeout);

  it('It deletes a record', async () => {
    page.on('request', interceptedRequest => {
      if (
        'http://pim.com/rest/reference_entity/designer/record/starck' === interceptedRequest.url() &&
        'DELETE' === interceptedRequest.method()
      ) {
        interceptedRequest.respond({
          status: 204,
        });
      }
    });

    await page.evaluate(async () => {
      const createRecordCode = require('akeneoreferenceentity/domain/model/record/code').createCode;
      const createReferenceEntityIdentifier = require('akeneoreferenceentity/domain/model/reference-entity/identifier')
        .createIdentifier;
      const remover = require('akeneoreferenceentity/infrastructure/remover/record').default;

      const recordCodeToDelete = createRecordCode('starck');
      const referenceEntityIdentifier = createReferenceEntityIdentifier('designer');

      return await remover.remove(referenceEntityIdentifier, recordCodeToDelete);
    });
  });

  it('It deletes all reference entity records', async () => {
    const requestContract = getRequestContract('Record/DeleteAll/ok.json');
    await listenRequest(page, requestContract);

    await page.evaluate(async () => {
      const createReferenceEntityIdentifier = require('akeneoreferenceentity/domain/model/reference-entity/identifier')
        .createIdentifier;
      const remover = require('akeneoreferenceentity/infrastructure/remover/record').default;

      const referenceEntityIdentifier = createReferenceEntityIdentifier('designer');

      return await remover.removeAll(referenceEntityIdentifier);
    });
  });
});
