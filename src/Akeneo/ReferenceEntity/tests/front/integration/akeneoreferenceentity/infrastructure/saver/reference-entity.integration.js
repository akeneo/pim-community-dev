const timeout = 5000;

describe('Akeneoreferenceentity > infrastructure > saver > reference-entity', () => {
  let page = global.__PAGE__;

  beforeEach(async () => {
    await page.reload();
  }, timeout);

  it('It saves a reference entity', async () => {
    page.on('request', interceptedRequest => {
      if (
        'http://pim.com/rest/reference_entity/sofa' === interceptedRequest.url() &&
        'POST' === interceptedRequest.method()
      ) {
        interceptedRequest.respond({
          status: 204,
        });
      }
    });

    const response = await page.evaluate(async () => {
      const createReferenceEntity = require('akeneoreferenceentity/domain/model/reference-entity/reference-entity')
        .createReferenceEntity;
      const createIdentifier = require('akeneoreferenceentity/domain/model/reference-entity/identifier')
        .createIdentifier;
      const createLabelCollection = require('akeneoreferenceentity/domain/model/label-collection')
        .createLabelCollection;
      const createAttributeReference = require('akeneoreferenceentity/domain/model/attribute/attribute-reference')
        .createAttributeReference;
      const Image = require('akeneoreferenceentity/domain/model/file').default;

      const savedSofa = createReferenceEntity(
        createIdentifier('sofa'),
        createLabelCollection({en_US: 'Sofa', fr_FR: 'Canapé'}),
        Image.createEmpty(),
        createAttributeReference(null),
        createAttributeReference(null)
      );
      const saver = require('akeneoreferenceentity/infrastructure/saver/reference-entity').default;

      return await saver.save(savedSofa);
    });

    expect(response).toEqual(undefined);
  });

  it('It creates a reference entity', async () => {
    page.on('request', interceptedRequest => {
      if (
        'http://pim.com/rest/reference_entity' === interceptedRequest.url() &&
        'POST' === interceptedRequest.method() &&
        'sofa' === JSON.parse(interceptedRequest.postData()).identifier
      ) {
        interceptedRequest.respond({
          status: 204,
        });
      }
    });

    const response = await page.evaluate(async () => {
      const createReferenceEntity = require('akeneoreferenceentity/domain/model/reference-entity/reference-entity')
        .createReferenceEntity;
      const createIdentifier = require('akeneoreferenceentity/domain/model/reference-entity/identifier')
        .createIdentifier;
      const createLabelCollection = require('akeneoreferenceentity/domain/model/label-collection')
        .createLabelCollection;
      const createAttributeReference = require('akeneoreferenceentity/domain/model/attribute/attribute-reference')
        .createAttributeReference;
      const Image = require('akeneoreferenceentity/domain/model/file').default;
      const saver = require('akeneoreferenceentity/infrastructure/saver/reference-entity').default;

      const sofaCreated = createReferenceEntity(
        createIdentifier('sofa'),
        createLabelCollection({en_US: 'Sofa', fr_FR: 'Canapé'}),
        Image.createEmpty(),
        createAttributeReference(null),
        createAttributeReference(null)
      );

      return await saver.create(sofaCreated);
    });

    expect(response).toEqual(undefined);
  });

  it('It returns errors when we create an invalid reference entity', async () => {
    const responseMessage = [
      {
        messageTemplate: 'This value should not be blank.',
        parameters: {
          '{{ value }}': '',
        },
        plural: null,
        message: 'This value should not be blank.',
        root: {
          identifier: '',
          labels: {
            en_US: 'deefef',
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
        'http://pim.com/rest/reference_entity' === interceptedRequest.url() &&
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
      const createReferenceEntity = require('akeneoreferenceentity/domain/model/reference-entity/reference-entity')
        .createReferenceEntity;
      const createIdentifier = require('akeneoreferenceentity/domain/model/reference-entity/identifier')
        .createIdentifier;
      const createLabelCollection = require('akeneoreferenceentity/domain/model/label-collection')
        .createLabelCollection;
      const createAttributeReference = require('akeneoreferenceentity/domain/model/attribute/attribute-reference')
        .createAttributeReference;
      const Image = require('akeneoreferenceentity/domain/model/file').default;
      const saver = require('akeneoreferenceentity/infrastructure/saver/reference-entity').default;

      const sofaCreated = createReferenceEntity(
        createIdentifier('invalid/identifier'),
        createLabelCollection({en_US: 'Sofa', fr_FR: 'Canapé'}),
        Image.createEmpty(),
        createAttributeReference(null),
        createAttributeReference(null)
      );

      return await saver.create(sofaCreated);
    });

    expect(JSON.stringify(response)).toEqual(JSON.stringify(responseMessage));
  });
});
