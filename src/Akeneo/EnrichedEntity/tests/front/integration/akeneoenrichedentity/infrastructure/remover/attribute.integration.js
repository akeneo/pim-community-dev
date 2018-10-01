const timeout = 5000;

describe('Akeneoreferenceentity > infrastructure > remover > attribute', () => {
  let page = global.__PAGE__;

  beforeEach(async () => {
    await page.reload();
  }, timeout);

  it('It deletes an attribute', async () => {
    page.on('request', interceptedRequest => {
      if (
        'http://pim.com/rest/reference_entity/designer/attribute/name_1234' === interceptedRequest.url() &&
        'DELETE' === interceptedRequest.method()
      ) {
        interceptedRequest.respond({
          status: 204,
        });
      }
    });

    await page.evaluate(async () => {
      const createIdentifier = require('akeneoreferenceentity/domain/model/attribute/identifier').createIdentifier;
      const createReferenceEntityIdentifier = require('akeneoreferenceentity/domain/model/reference-entity/identifier')
        .createIdentifier;
      const remover = require('akeneoreferenceentity/infrastructure/remover/attribute').default;

      const attributeIdentifierToDelete = createIdentifier('name_1234');
      const referenceEntityIdentifierToDelete = createReferenceEntityIdentifier('designer');

      return await remover.remove(referenceEntityIdentifierToDelete, attributeIdentifierToDelete);
    });
  });
});
