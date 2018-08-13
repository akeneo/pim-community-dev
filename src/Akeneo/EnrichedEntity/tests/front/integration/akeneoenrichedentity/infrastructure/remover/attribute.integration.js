const timeout = 5000;

describe('Akeneoenrichedentity > infrastructure > remover > attribute', () => {
  let page = global.__PAGE__;

  beforeEach(async () => {
    await page.reload();
  }, timeout);

  it('It deletes an attribute record', async () => {
    page.on('request', interceptedRequest => {
      if (
        'http://pim.com/rest/enriched_entity/designer/attribute/description' === interceptedRequest.url() &&
        'DELETE' === interceptedRequest.method() &&
        'starck' === JSON.parse(interceptedRequest.postData()).identifier.identifier
      ) {
        interceptedRequest.respond({
          status: 204
        });
      }
    });

    const response = await page.evaluate(async () => {
      const denormalizeAttribute = require('akeneoenrichedentity/domain/model/attribute/attribute').denormalizeAttribute;
      // const createAttributeCode = require('akeneoenrichedentity/domain/model/attribute/code').createCode;
      // const createIdentifier = require('akeneoenrichedentity/domain/model/attribute/identifier').createIdentifier;
      // const createEnrichedEntityIdentifier = require('akeneoenrichedentity/domain/model/enriched-entity/identifier')
      //   .createIdentifier;
      // const createLabelCollection = require('akeneoenrichedentity/domain/model/label-collection')
      //   .createLabelCollection;
      const remover = require('akeneoenrichedentity/infrastructure/remover/attribute').default;

      const attributeCreated = denormalizeAttribute({
          type: 'text',
          identifier: {identifier: 'description', enrichedEntityIdentifier: 'designer'},
          enrichedEntityIdentifier: 'designer',
          code: 'description',
          labels: []
    });

      return await remover.remove(attributeCreated);
    });

    expect(response).toEqual(undefined);
  });
});
