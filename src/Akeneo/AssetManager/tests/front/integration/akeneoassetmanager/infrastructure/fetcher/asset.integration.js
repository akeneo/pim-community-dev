const timeout = 5000;
const {getRequestContract, listenRequest} = require('../../../../acceptance/cucumber/tools');

describe('Akeneoassetfamily > infrastructure > fetcher > asset', () => {
  let page = global.__PAGE__;

  beforeEach(async () => {
    await page.reload();
  }, timeout);

  it('It fetches one asset', async () => {
    const requestContract = getRequestContract('Asset/AssetDetails/ok.json');

    await listenRequest(page, requestContract);

    const response = await page.evaluate(async () => {
      const fetcher = require('akeneoassetmanager/infrastructure/fetcher/asset').default;

      return await fetcher.fetch('designer', 'starck');
    });

    expect(response).toEqual({
      permission: {edit: true, assetFamilyIdentifier: 'designer'},
      asset: {
        code: 'starck',
        identifier: 'designer_starck_a1677570-a278-444b-ab46-baa1db199392',
        attributeAsMainMediaIdentifier: 'portrait_designer_fingerprint',
        image: null,
        labelCollection: {fr_FR: 'Philippe Starck'},
        assetFamilyIdentifier: 'designer',
        valueCollection: {
          values: [
            {
              attribute: {
                code: 'name',
                identifier: 'name_designer_fingerprint',
                isRequired: false,
                isRichTextEditor: false,
                isTextarea: false,
                labelCollection: {fr_FR: 'Nom'},
                maxLength: 25,
                order: 0,
                assetFamilyIdentifier: 'designer',
                regularExpression: null,
                type: 'text',
                validationRule: 'none',
                valuePerChannel: false,
                valuePerLocale: false,
              },
              channel: null,
              data: {textData: 'Philippe Starck'},
              locale: null,
            },
            {
              attribute: {
                code: 'description',
                identifier: 'description_designer_fingerprint',
                isRequired: false,
                isRichTextEditor: true,
                isTextarea: true,
                labelCollection: {fr_FR: 'Description'},
                maxLength: 25,
                order: 1,
                assetFamilyIdentifier: 'designer',
                regularExpression: null,
                type: 'text',
                validationRule: 'none',
                valuePerChannel: false,
                valuePerLocale: true,
              },
              channel: null,
              data: {textData: ''},
              locale: 'en_US',
            },
            {
              attribute: {
                code: 'description',
                identifier: 'description_designer_fingerprint',
                isRequired: false,
                isRichTextEditor: true,
                isTextarea: true,
                labelCollection: {fr_FR: 'Description'},
                maxLength: 25,
                order: 1,
                assetFamilyIdentifier: 'designer',
                regularExpression: null,
                type: 'text',
                validationRule: 'none',
                valuePerChannel: false,
                valuePerLocale: true,
              },
              channel: null,
              data: {textData: ''},
              locale: 'fr_FR',
            },
            {
              attribute: {
                code: 'website',
                identifier: 'website_designer_fingerprint',
                isRequired: true,
                isRichTextEditor: false,
                isTextarea: false,
                labelCollection: {fr_FR: 'Website'},
                maxLength: 25,
                order: 2,
                assetFamilyIdentifier: 'designer',
                regularExpression: null,
                type: 'text',
                validationRule: 'url',
                valuePerChannel: false,
                valuePerLocale: false,
              },
              channel: null,
              data: {textData: ''},
              locale: null,
            },
            {
              attribute: {
                allowedExtensions: ['png'],
                code: 'portrait',
                identifier: 'portrait_designer_fingerprint',
                isRequired: false,
                labelCollection: {en_US: 'Portrait', fr_FR: 'Image autobiographique'},
                maxFileSize: '200.10',
                mediaType: 'image',
                order: 3,
                assetFamilyIdentifier: 'designer',
                type: 'media_file',
                valuePerChannel: false,
                valuePerLocale: false,
              },
              channel: null,
              data: {fileData: null},
              locale: null,
            },
            {
              attribute: {
                code: 'age',
                assetFamilyIdentifier: 'designer',
                identifier: 'age_designer_fingerprint',
                decimalsAllowed: false,
                isRequired: false,
                labelCollection: {en_US: 'Age', fr_FR: 'Age'},
                maxValue: '20',
                minValue: '10',
                order: 4,
                type: 'number',
                valuePerChannel: false,
                valuePerLocale: false,
              },
              channel: null,
              data: {numberData: ''},
              locale: null,
            },
          ],
        },
      },
    });
  });

  it('It search for assets', async () => {
    const requestContract = getRequestContract('Asset/Search/ok.json');

    await listenRequest(page, requestContract);

    const response = await page.evaluate(async () => {
      const fetcher = require('akeneoassetmanager/infrastructure/fetcher/asset').default;

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
            field: 'asset_family',
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
          asset_family_identifier: 'designer',
          image: [
            {
              attribute: 'image_designer_ca1857d521a3e5dbc487791da82b7428',
              locale: null,
              channel: null,
              data: {filePath: '', originalFilename: ''},
            },
          ],
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
                  'city_paris_bf11a6b3-3e46-4bbf-b35c-814a0020c717': {
                    labels: {
                      en_US: 'Paris',
                    },
                    code: 'paris',
                  },
                },
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
          asset_family_identifier: 'designer',
          image: [
            {
              attribute: 'image_designer_ca1857d521a3e5dbc487791da82b7428',
              locale: null,
              channel: null,
              data: {filePath: '', originalFilename: ''},
            },
          ],
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
                  'city_paris_bf11a6b3-3e46-4bbf-b35c-814a0020c717': {
                    labels: {
                      en_US: 'Paris',
                    },
                    code: 'paris',
                  },
                },
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

  it('It search for empty assets', async () => {
    const requestContract = getRequestContract('Asset/Search/no_result.json');

    await listenRequest(page, requestContract);

    const response = await page.evaluate(async () => {
      const fetcher = require('akeneoassetmanager/infrastructure/fetcher/asset').default;

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
            field: 'asset_family',
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
