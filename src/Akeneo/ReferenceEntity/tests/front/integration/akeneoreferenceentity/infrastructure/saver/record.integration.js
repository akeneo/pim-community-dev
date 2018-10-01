const timeout = 5000;

describe('Akeneoreferenceentity > infrastructure > saver > record', () => {
  let page = global.__PAGE__;

  beforeEach(async () => {
    await page.reload();
  }, timeout);

  it('It creates a record', async () => {
    page.on('request', interceptedRequest => {
      if (
        'http://pim.com/rest/reference_entity/designer/record' === interceptedRequest.url() &&
        'POST' === interceptedRequest.method() &&
        'designer_starck_1' === JSON.parse(interceptedRequest.postData()).identifier
      ) {
        interceptedRequest.respond({
          status: 204,
        });
      }
    });

    const response = await page.evaluate(async () => {
      const createRecord = require('akeneoreferenceentity/domain/model/record/record').createRecord;
      const createRecordCode = require('akeneoreferenceentity/domain/model/record/code').createCode;
      const createValueCollection = require('akeneoreferenceentity/domain/model/record/value-collection')
        .createValueCollection;
      const createIdentifier = require('akeneoreferenceentity/domain/model/record/identifier').createIdentifier;
      const createReferenceEntityIdentifier = require('akeneoreferenceentity/domain/model/reference-entity/identifier')
        .createIdentifier;
      const Image = require('akeneoreferenceentity/domain/model/file').default;
      const createLabelCollection = require('akeneoreferenceentity/domain/model/label-collection')
        .createLabelCollection;
      const saver = require('akeneoreferenceentity/infrastructure/saver/record').default;

      const recordCreated = createRecord(
        createIdentifier('designer_starck_1'),
        createReferenceEntityIdentifier('designer'),
        createRecordCode('starck'),
        createLabelCollection({en_US: 'Stylist', fr_FR: 'Styliste'}),
        Image.createEmpty(),
        createValueCollection([])
      );

      return await saver.create(recordCreated);
    });

    expect(response).toEqual(undefined);
  });

  it('It returns errors when we create an invalid record', async () => {
    const responseMessage = [
      {
        messageTemplate: 'This field may only contain letters, numbers and underscores.',
        parameters: {
          '{{ value }}': '/',
        },
        plural: null,
        message: 'pim_reference_entity.record.validation.identifier.pattern',
        root: {
          identifier: 'invalid/identifier',
          labels: {
            en_US: 'Stylist',
            fr_FR: 'Styliste',
          },
        },
        propertyPath: 'identifier',
        invalidValue: '',
        constraint: {
          defaultOption: null,
          requiredOptions: [],
          targets: 'property',
          payload: null,
        },
        cause: null,
        code: null,
      },
    ];

    page.on('request', interceptedRequest => {
      if (
        'http://pim.com/rest/reference_entity/designer/record' === interceptedRequest.url() &&
        'POST' === interceptedRequest.method() &&
        'invalid/identifier' === JSON.parse(interceptedRequest.postData()).identifier
      ) {
        interceptedRequest.respond({
          status: 400,
          contentType: 'application/json',
          body: JSON.stringify(responseMessage),
        });
      }
    });

    const response = await page.evaluate(async () => {
      const createRecord = require('akeneoreferenceentity/domain/model/record/record').createRecord;
      const createRecordCode = require('akeneoreferenceentity/domain/model/record/code').createCode;
      const createIdentifier = require('akeneoreferenceentity/domain/model/record/identifier').createIdentifier;
      const createReferenceEntityIdentifier = require('akeneoreferenceentity/domain/model/reference-entity/identifier')
        .createIdentifier;
      const Image = require('akeneoreferenceentity/domain/model/file').default;
      const createLabelCollection = require('akeneoreferenceentity/domain/model/label-collection')
        .createLabelCollection;
      const createValueCollection = require('akeneoreferenceentity/domain/model/record/value-collection')
        .createValueCollection;
      const saver = require('akeneoreferenceentity/infrastructure/saver/record').default;

      const recordCreated = createRecord(
        createIdentifier('invalid/identifier'),
        createReferenceEntityIdentifier('designer'),
        createRecordCode('invalid/identifier'),
        createLabelCollection({en_US: 'Stylist', fr_FR: 'Styliste'}),
        Image.createEmpty(),
        createValueCollection([])
      );

      return await saver.create(recordCreated);
    });

    expect(JSON.stringify(response)).toEqual(JSON.stringify(responseMessage));
  });
});
