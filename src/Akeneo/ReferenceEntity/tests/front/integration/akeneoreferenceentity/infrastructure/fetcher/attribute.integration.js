const timeout = 5000;

describe('Akeneoreferenceentity > infrastructure > fetcher > attribute', () => {
  let page = global.__PAGE__;

  beforeEach(async () => {
    await page.reload();
  }, timeout);

  it('It lists the attributes of a reference entity', async () => {
    page.on('request', interceptedRequest => {
      if (
        'http://pim.com/rest/reference_entity/designer/attribute' === interceptedRequest.url() &&
        'GET' === interceptedRequest.method()
      ) {
        interceptedRequest.respond({
          contentType: 'application/json',
          body: JSON.stringify([
            {
              identifier: 'description_1234',
              reference_entity_identifier: 'designer',
              code: 'description',
              is_required: true,
              order: 0,
              value_per_locale: true,
              value_per_channel: false,
              type: 'text',
              labels: {
                en_US: 'Description',
              },
              max_length: 12,
              is_textarea: true,
              is_rich_text_editor: false,
              validation_rule: 'none',
              regular_expression: null,
            },
            {
              identifier: 'side_view_1234',
              reference_entity_identifier: 'designer',
              code: 'side_view',
              is_required: false,
              order: 1,
              value_per_locale: true,
              value_per_channel: false,
              type: 'image',
              labels: {
                en_US: 'Side view',
              },
              max_file_size: '123.4',
              allowed_extensions: ['jpg', 'png'],
            },
          ]),
        });
      }
    });

    const response = await page.evaluate(async () => {
      const fetcher = require('akeneoreferenceentity/infrastructure/fetcher/attribute').default;
      const identifierModule = 'akeneoreferenceentity/domain/model/reference-entity/identifier';
      const referenceEntityIdentifier = require(identifierModule).createIdentifier('designer');

      return await fetcher.fetchAll(referenceEntityIdentifier);
    });

    // Missing properties such as "maxFileSize" and "AllowedExtensions"
    expect(response).toEqual([
      {
        code: {
          code: 'description',
        },
        referenceEntityIdentifier: {
          identifier: 'designer',
        },
        identifier: {
          identifier: 'description_1234',
        },
        isRichTextEditor: {
          isRichTextEditor: false,
        },
        isTextarea: {
          isTextarea: true,
        },
        labelCollection: {labels: {en_US: 'Description'}},
        maxLength: {
          maxLength: 12,
        },
        regularExpression: {
          regularExpression: null,
        },
        validationRule: {
          validationRule: 'none',
        },
        order: 0,
        isRequired: true,
        type: 'text',
        valuePerChannel: false,
        valuePerLocale: true,
      },
      {
        code: {
          code: 'side_view',
        },
        referenceEntityIdentifier: {
          identifier: 'designer',
        },
        identifier: {
          identifier: 'side_view_1234',
        },
        labelCollection: {
          labels: {
            en_US: 'Side view',
          },
        },
        order: 1,
        isRequired: false,
        type: 'image',
        valuePerChannel: false,
        valuePerLocale: true,
        allowedExtensions: {
          allowedExtensions: ['jpg', 'png'],
        },
        maxFileSize: {
          maxFileSize: '123.4',
        },
      },
    ]);
  });
});
