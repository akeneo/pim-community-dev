const timeout = 5000;

describe('Akeneoenrichedentity > infrastructure > saver > enriched-entity', () => {
  let page = global.__PAGE__;

  beforeEach(async () => {
    await page.reload();
  }, timeout);

  it('It saves an enriched entity', async () => {
    page.on('request', interceptedRequest => {
      if (
        'http://pim.com/rest/enriched_entity/sofa' === interceptedRequest.url() &&
        'POST' === interceptedRequest.method()
      ) {
        interceptedRequest.respond({
          status: 204
        });
      }
    });

    const response = await page.evaluate(async () => {
      const createEnrichedEntity = require('akeneoenrichedentity/domain/model/enriched-entity/enriched-entity')
        .createEnrichedEntity;
      const createIdentifier = require('akeneoenrichedentity/domain/model/enriched-entity/identifier').createIdentifier;
      const createLabelCollection = require('akeneoenrichedentity/domain/model/label-collection').createLabelCollection;

      const savedSofa = createEnrichedEntity(
        createIdentifier('sofa'),
        createLabelCollection({en_US: 'Sofa', fr_FR: 'Canapé'})
      );
      const saver = require('akeneoenrichedentity/infrastructure/saver/enriched-entity').default;

      return await saver.save(savedSofa);
    });

    expect(response).toEqual(undefined);
  });

  it('It creates an enriched entity', async () => {
    page.on('request', interceptedRequest => {
      if (
        'http://pim.com/rest/enriched_entity' === interceptedRequest.url() &&
        'POST' === interceptedRequest.method() &&
        'sofa' === JSON.parse(interceptedRequest.postData()).identifier
      ) {
        interceptedRequest.respond({
          status: 204
        });
      }
    });

    const response = await page.evaluate(async () => {
      const createEnrichedEntity = require('akeneoenrichedentity/domain/model/enriched-entity/enriched-entity')
        .createEnrichedEntity;
      const createIdentifier = require('akeneoenrichedentity/domain/model/enriched-entity/identifier').createIdentifier;
      const createLabelCollection = require('akeneoenrichedentity/domain/model/label-collection').createLabelCollection;
      const saver = require('akeneoenrichedentity/infrastructure/saver/enriched-entity').default;

      const sofaCreated = createEnrichedEntity(
        createIdentifier('sofa'),
        createLabelCollection({en_US: 'Sofa', fr_FR: 'Canapé'})
      );

      return await saver.create(sofaCreated);
    });

    expect(response).toEqual(undefined);
  });

  it('It returns errors when we create an invalid enriched entity', async () => {
    const responseMessage = [{
      messageTemplate: 'This value should not be blank.',
      parameters: {
        '{{ value }}': ''
      },
      plural: null,
      message: 'This value should not be blank.',
      root: {
        identifier: '',
        labels: {
          en_US: 'deefef'
        }
      },
      propertyPath: 'identifier',
      invalidValue: '',
      constraint: {
        defaultOption: null,
        requiredOptions: [],
        targets: 'property',
        payload: null
      },
      cause: null,
      code: null
    }];

    page.on('request', interceptedRequest => {
      if (
          'http://pim.com/rest/enriched_entity' === interceptedRequest.url() &&
          'POST' === interceptedRequest.method() &&
          'invalid/identifier' === JSON.parse(interceptedRequest.postData()).identifier
      ) {
        interceptedRequest.respond({
          status: 400,
          contentType: 'application/json',
          body: JSON.stringify(responseMessage)
        });
      }
    });

    const response = await page.evaluate(async () => {
      const createEnrichedEntity = require('akeneoenrichedentity/domain/model/enriched-entity/enriched-entity')
          .createEnrichedEntity;
      const createIdentifier = require('akeneoenrichedentity/domain/model/enriched-entity/identifier').createIdentifier;
      const createLabelCollection = require('akeneoenrichedentity/domain/model/label-collection').createLabelCollection;
      const saver = require('akeneoenrichedentity/infrastructure/saver/enriched-entity').default;

      const sofaCreated = createEnrichedEntity(
          createIdentifier('invalid/identifier'),
          createLabelCollection({en_US: 'Sofa', fr_FR: 'Canapé'})
      );

      return await saver.create(sofaCreated);
    });

    expect(JSON.stringify(response)).toEqual(JSON.stringify(responseMessage));
  });
});
