const timeout = 5000;
const {getRequestContract, listenRequest} = require('../../../../acceptance/cucumber/tools');

describe('Akeneoreferenceentity > infrastructure > fetcher > record', () => {
  let page = global.__PAGE__;

  beforeEach(async () => {
    await page.reload();
  }, timeout);

  it('It fetches one record', async () => {
    const requestContract = getRequestContract('Record/RecordDetails/ok.json');

    await listenRequest(page, requestContract);

    const response = await page.evaluate(async () => {
      const fetcher = require('akeneoreferenceentity/infrastructure/fetcher/record').default;

      const referenceEntityIdentifierModule = 'akeneoreferenceentity/domain/model/reference-entity/identifier';
      const referenceEntityIdentifier = require(referenceEntityIdentifierModule).createIdentifier('designer');
      const recordIdentifier = require(referenceEntityIdentifierModule).createIdentifier('starck');

      return await fetcher.fetch(referenceEntityIdentifier, recordIdentifier);
    });

    expect(response).toEqual({
      permission: {edit: true, referenceEntityIdentifier: 'designer'},
      record: {
        code: {code: 'starck'},
        identifier: {identifier: 'designer_starck_a1677570-a278-444b-ab46-baa1db199392'},
        image: {},
        labelCollection: {labels: {fr_FR: 'Philippe Starck'}},
        referenceEntityIdentifier: {identifier: 'designer'},
        valueCollection: {
          values: [
            {
              attribute: {
                code: {code: 'name'},
                identifier: {identifier: 'name_designer_fingerprint'},
                isRequired: false,
                isRichTextEditor: {isRichTextEditor: false},
                isTextarea: {isTextarea: false},
                labelCollection: {labels: {fr_FR: 'Nom'}},
                maxLength: {maxLength: 25},
                order: 0,
                referenceEntityIdentifier: {identifier: 'designer'},
                regularExpression: {regularExpression: null},
                type: 'text',
                validationRule: {validationRule: 'none'},
                valuePerChannel: false,
                valuePerLocale: false,
              },
              channel: {channelReference: null},
              data: {textData: 'Philippe Starck'},
              locale: {localeReference: null},
            },
            {
              attribute: {
                code: {code: 'description'},
                identifier: {identifier: 'description_designer_fingerprint'},
                isRequired: false,
                isRichTextEditor: {isRichTextEditor: true},
                isTextarea: {isTextarea: true},
                labelCollection: {labels: {fr_FR: 'Description'}},
                maxLength: {maxLength: 25},
                order: 1,
                referenceEntityIdentifier: {identifier: 'designer'},
                regularExpression: {regularExpression: null},
                type: 'text',
                validationRule: {validationRule: 'none'},
                valuePerChannel: false,
                valuePerLocale: true,
              },
              channel: {channelReference: null},
              data: {textData: ''},
              locale: {localeReference: 'en_US'},
            },
            {
              attribute: {
                code: {code: 'description'},
                identifier: {identifier: 'description_designer_fingerprint'},
                isRequired: false,
                isRichTextEditor: {isRichTextEditor: true},
                isTextarea: {isTextarea: true},
                labelCollection: {labels: {fr_FR: 'Description'}},
                maxLength: {maxLength: 25},
                order: 1,
                referenceEntityIdentifier: {identifier: 'designer'},
                regularExpression: {regularExpression: null},
                type: 'text',
                validationRule: {validationRule: 'none'},
                valuePerChannel: false,
                valuePerLocale: true,
              },
              channel: {channelReference: null},
              data: {textData: ''},
              locale: {localeReference: 'fr_FR'},
            },
            {
              attribute: {
                code: {code: 'website'},
                identifier: {identifier: 'website_designer_fingerprint'},
                isRequired: true,
                isRichTextEditor: {isRichTextEditor: false},
                isTextarea: {isTextarea: false},
                labelCollection: {labels: {fr_FR: 'Website'}},
                maxLength: {maxLength: 25},
                order: 2,
                referenceEntityIdentifier: {identifier: 'designer'},
                regularExpression: {regularExpression: null},
                type: 'text',
                validationRule: {validationRule: 'url'},
                valuePerChannel: false,
                valuePerLocale: false,
              },
              channel: {channelReference: null},
              data: {textData: ''},
              locale: {localeReference: null},
            },
            {
              attribute: {
                allowedExtensions: {allowedExtensions: ['png']},
                code: {code: 'portrait'},
                identifier: {identifier: 'portrait_designer_fingerprint'},
                isRequired: false,
                labelCollection: {labels: {en_US: 'Portrait', fr_FR: 'Image autobiographique'}},
                maxFileSize: {maxFileSize: '200.10'},
                order: 3,
                referenceEntityIdentifier: {identifier: 'designer'},
                type: 'image',
                valuePerChannel: false,
                valuePerLocale: false,
              },
              channel: {channelReference: null},
              data: {fileData: {}},
              locale: {localeReference: null},
            },
          ],
        },
      },
    });
  });

  it('It search for records', async () => {
    const requestContract = getRequestContract('Record/Search/ok.json');

    await listenRequest(page, requestContract);

    const response = await page.evaluate(async () => {
      const fetcher = require('akeneoreferenceentity/infrastructure/fetcher/record').default;

      return await fetcher.search({
        locale: 'en_US',
        channel: 'ecommerce',
        size: 200,
        page: 0,
        filters: [
          {
            field: 'full_text',
            operator: '=',
            value: 's',
            context: {},
          },
          {
            field: 'reference_entity',
            operator: '=',
            value: 'designer',
            context: {},
          },
        ],
      });
    });

    expect(response).toEqual({
      items: [
        {
          code: 'dyson',
          identifier: 'designer_dyson_01afdc3e-3ecf-4a86-85ef-e81b2d6e95fd',
          labels: {en_US: 'Dyson', fr_FR: 'Dyson'},
          reference_entity_identifier: 'designer',
          image: null,
          values: {
            label_designer_d00de54460082b239164135175588647_en_US: {
              attribute: 'label_designer_d00de54460082b239164135175588647',
              channel: null,
              data: 'Dyson',
              locale: 'en_US',
            },
            label_designer_d00de54460082b239164135175588647_fr_FR: {
              attribute: 'label_designer_d00de54460082b239164135175588647',
              channel: null,
              data: 'Dyson',
              locale: 'fr_FR',
            },
            city_designer_79eb100099b9a8bf52609e00b7ee307e: {
              attribute: 'city_designer_79eb100099b9a8bf52609e00b7ee307e',
              channel: null,
              context: {
                labels: {
                  "city_paris_bf11a6b3-3e46-4bbf-b35c-814a0020c717": "Paris"
                }
              },
              data: 'city_paris_bf11a6b3-3e46-4bbf-b35c-814a0020c717',
              locale: null,
            },
            colors_designer_52609e00b7ee307e79eb100099b9a8bf: {
              attribute: 'colors_designer_52609e00b7ee307e79eb100099b9a8bf',
              channel: null,
              data: 'red',
              locale: null,
            },
          },
          completeness: {
            complete: 0,
            required: 1,
          },
        },
        {
          code: 'starck',
          identifier: 'designer_starck_29aea250-bc94-49b2-8259-bbc116410eb2',
          labels: {en_US: 'Starck'},
          reference_entity_identifier: 'designer',
          image: null,
          values: {
            'description_designer_29aea250-bc94-49b2-8259-bbc116410eb2_ecommerce_en_US': {
              attribute: 'description_designer_29aea250-bc94-49b2-8259-bbc116410eb2',
              channel: 'ecommerce',
              data: 'an awesome designer!',
              locale: 'en_US',
            },
            label_designer_d00de54460082b239164135175588647_en_US: {
              attribute: 'label_designer_d00de54460082b239164135175588647',
              channel: null,
              data: 'Starck',
              locale: 'en_US',
            },
            city_designer_79eb100099b9a8bf52609e00b7ee307e: {
              attribute: 'city_designer_79eb100099b9a8bf52609e00b7ee307e',
              channel: null,
              context: {
                labels: {
                  "city_paris_bf11a6b3-3e46-4bbf-b35c-814a0020c717": "Paris"
                }
              },
              data: 'city_paris_bf11a6b3-3e46-4bbf-b35c-814a0020c717',
              locale: null,
            },
            colors_designer_52609e00b7ee307e79eb100099b9a8bf: {
              attribute: 'colors_designer_52609e00b7ee307e79eb100099b9a8bf',
              channel: null,
              data: 'red',
              locale: null,
            },
          },
          completeness: {
            complete: 0,
            required: 1,
          },
        },
      ],
      matchesCount: 2,
      totalCount: 3,
    });
  });

  it('It search for empty records', async () => {
    const requestContract = getRequestContract('Record/Search/no_result.json');

    await listenRequest(page, requestContract);

    const response = await page.evaluate(async () => {
      const fetcher = require('akeneoreferenceentity/infrastructure/fetcher/record').default;

      return await fetcher.search({
        locale: 'en_US',
        channel: 'ecommerce',
        size: 200,
        page: 0,
        filters: [
          {
            field: 'full_text',
            operator: '=',
            value: 'search',
            context: {},
          },
          {
            field: 'reference_entity',
            operator: '=',
            value: 'designer',
            context: {},
          },
        ],
      });
    });

    expect(response).toEqual({
      items: [],
      matchesCount: 0,
      totalCount: 3,
    });
  });
});
