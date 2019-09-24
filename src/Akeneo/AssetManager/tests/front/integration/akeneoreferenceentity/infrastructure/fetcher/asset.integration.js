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

      const assetFamilyIdentifierModule = 'akeneoassetmanager/domain/model/asset-family/identifier';
      const assetFamilyIdentifier = require(assetFamilyIdentifierModule).createIdentifier('designer');
      const assetIdentifier = require(assetFamilyIdentifierModule).createIdentifier('starck');

      return await fetcher.fetch(assetFamilyIdentifier, assetIdentifier);
    });

    expect(response).toEqual({
      permission: {edit: true, assetFamilyIdentifier: 'designer'},
      asset: {
        code: {code: 'starck'},
        identifier: {identifier: 'designer_starck_a1677570-a278-444b-ab46-baa1db199392'},
        image: {},
        labelCollection: {labels: {fr_FR: 'Philippe Starck'}},
        assetFamilyIdentifier: {identifier: 'designer'},
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
                assetFamilyIdentifier: {identifier: 'designer'},
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
                assetFamilyIdentifier: {identifier: 'designer'},
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
                assetFamilyIdentifier: {identifier: 'designer'},
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
                assetFamilyIdentifier: {identifier: 'designer'},
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
                assetFamilyIdentifier: {identifier: 'designer'},
                type: 'image',
                valuePerChannel: false,
                valuePerLocale: false,
              },
              channel: {channelReference: null},
              data: {fileData: {}},
              locale: {localeReference: null},
            },
            {
              attribute: {
                code: {code: 'age'},
                assetFamilyIdentifier: {identifier: 'designer'},
                identifier: {identifier: 'age_designer_fingerprint'},
                decimalsAllowed: {decimalsAllowed: false},
                isRequired: false,
                labelCollection: {labels: {en_US: 'Age', fr_FR: 'Age'}},
                maxValue: {maxValue: '20'},
                minValue: {minValue: '10'},
                order: 4,
                type: 'number',
                valuePerChannel: false,
                valuePerLocale: false,
              },
              channel: {channelReference: null},
              data: {numberData: ''},
              locale: {localeReference: null},
            }
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
            value: 'packshot',
            context: {},
          },
        ],
      });
    });

    expect(response).toEqual({
      items: [
        {
          code: 'backview',
          identifier: 'packshot_backview_01afdc3e-3ecf-4a86-85ef-e81b2d6e95fd',
          labels: {en_US: 'Backview', fr_FR: 'Backview'},
          asset_family_identifier: 'packshot',
          image: '/rest/asset_manager/image_preview/image_packshot_10b86252577a2f9691d90c162083c4a3/thumbnail?data=',
          values: {
            label_packshot_ef993aecc49d7dd11497cdafbb001287_en_US: {
              attribute: 'label_packshot_ef993aecc49d7dd11497cdafbb001287',
              channel: null,
              data: 'Backview',
              locale: 'en_US',
            },
            label_packshot_ef993aecc49d7dd11497cdafbb001287_fr_FR: {
              attribute: 'label_packshot_ef993aecc49d7dd11497cdafbb001287',
              channel: null,
              data: 'Backview',
              locale: 'fr_FR',
            }
          },
          completeness: {
            complete: 0,
            required: 0,
          },
        },
        {
          code: 'frontview',
          identifier: 'packshot_frontview_29aea250-bc94-49b2-8259-bbc116410eb2',
          labels: {en_US: 'Frontview'},
          asset_family_identifier: 'packshot',
          image: '/rest/asset_manager/image_preview/image_packshot_10b86252577a2f9691d90c162083c4a3/thumbnail?data=',
          values: {
            label_packshot_ef993aecc49d7dd11497cdafbb001287_en_US: {
              attribute: 'label_packshot_ef993aecc49d7dd11497cdafbb001287',
              channel: null,
              data: 'Frontview',
              locale: 'en_US',
            }
          },
          completeness: {
            complete: 0,
            required: 0,
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
            value: 'packshot',
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
