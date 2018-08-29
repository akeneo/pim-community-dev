const timeout = 5000;

describe('Akeneoenrichedentity > infrastructure > saver > record', () => {
  let page = global.__PAGE__;

  beforeEach(async () => {
    await page.reload();
  }, timeout);

  it('It creates a record', async () => {
    page.on('request', interceptedRequest => {
      if (
        'http://pim.com/rest/enriched_entity/designer/record' === interceptedRequest.url() &&
        'POST' === interceptedRequest.method() &&
        'starck' === JSON.parse(interceptedRequest.postData()).identifier
      ) {
        interceptedRequest.respond({
          status: 204
        });
      }
    });

    const response = await page.evaluate(async () => {
      const createRecord = require('akeneoenrichedentity/domain/model/record/record').createRecord;
      const createRecordCode = require('akeneoenrichedentity/domain/model/record/code').createCode;
      const createIdentifier = require('akeneoenrichedentity/domain/model/record/identifier').createIdentifier;
      const createEnrichedEntityIdentifier = require('akeneoenrichedentity/domain/model/enriched-entity/identifier')
        .createIdentifier;
      const createLabelCollection = require('akeneoenrichedentity/domain/model/label-collection')
        .createLabelCollection;
      const saver = require('akeneoenrichedentity/infrastructure/saver/record').default;

      const recordCreated = createRecord(
        createIdentifier('starck'),
        createEnrichedEntityIdentifier('designer'),
        createRecordCode('starck'),
        createLabelCollection({en_US: 'Stylist', fr_FR: 'Styliste'})
      );

      return await saver.create(recordCreated);
    });

    expect(response).toEqual(undefined);
  });

  it('It returns errors when we create an invalid record', async () => {
    const responseMessage = [{
      messageTemplate: 'This field may only contain letters, numbers and underscores.',
      parameters: {
        '{{ value }}': '/'
      },
      plural: null,
      message: 'pim_enriched_entity.record.validation.identifier.pattern',
      root: {
        identifier: 'invalid/identifier',
        labels: {
          en_US: 'Stylist',
          fr_FR: 'Styliste'
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
          'http://pim.com/rest/enriched_entity/designer/record' === interceptedRequest.url() &&
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
      const createRecord = require('akeneoenrichedentity/domain/model/record/record').createRecord;
      const createRecordCode = require('akeneoenrichedentity/domain/model/record/code').createCode;
      const createIdentifier = require('akeneoenrichedentity/domain/model/record/identifier').createIdentifier;
      const createEnrichedEntityIdentifier = require('akeneoenrichedentity/domain/model/enriched-entity/identifier')
        .createIdentifier;
      const createLabelCollection = require('akeneoenrichedentity/domain/model/label-collection')
        .createLabelCollection;
      const saver = require('akeneoenrichedentity/infrastructure/saver/record').default;

      const recordCreated = createRecord(
        createIdentifier('invalid/identifier'),
        createEnrichedEntityIdentifier('designer'),
        createRecordCode('invalid/identifier'),
        createLabelCollection({en_US: 'Stylist', fr_FR: 'Styliste'})
      );

      return await saver.create(recordCreated);
    });

    expect(JSON.stringify(response)).toEqual(JSON.stringify(responseMessage));
  });
});
