const timeout = 5000;
const {getRequestContract, listenRequest} = require('../../../../acceptance/cucumber/tools');
const AssetFamilyBuilder = require('../../../../common/builder/asset-family.js');

describe('Akeneoassetfamily > infrastructure > fetcher > asset', () => {
  let page = global.__PAGE__;

  beforeEach(async () => {
    await page.reload();
  }, timeout);

  it('It fetches one asset', async () => {
    const requestContract = getRequestContract('Asset/AssetDetails/ok.json');
    await listenRequest(page, requestContract);

    page.on('request', interceptedRequest => {
      if (
        'http://pim.com/rest/asset_manager/designer' === interceptedRequest.url() &&
        'GET' === interceptedRequest.method()
      ) {
        const assetFamily = new AssetFamilyBuilder()
          .withIdentifier('designer')
          .withLabels({
            en_US: 'Designer',
            fr_FR: 'Designer',
          })
          .withImage({
            filePath: '/path/designer.jpg',
            originalFilename: 'designer.jpg',
          })
          .withAttributes([])
          .withAttributeAsMainMedia('')
          .withAttributeAsLabel('')
          .build();

        interceptedRequest.respond({
          contentType: 'application/json',
          body: JSON.stringify(assetFamily),
        });
      }
    });

    const response = await page.evaluate(async () => {
      // Sometimes this test fails on circle ci. This wait should mitigate that
      await new Promise((resolve) => setTimeout(resolve, 500));

      const fetcher = require('akeneoassetmanager/infrastructure/fetcher/asset').default;

      return await fetcher.fetch('designer', 'starck');
    });

    expect(response).toEqual({
      permission: {edit: true, assetFamilyIdentifier: 'designer'},
      asset: {
        code: 'starck',
        identifier: 'designer_starck_a1677570-a278-444b-ab46-baa1db199392',
        labels: {fr_FR: 'Philippe Starck'},
        assetFamily: {
          assetCount: 123,
          attributeAsLabel: '',
          attributeAsMainMedia: '',
          attributes: [],
          code: 'designer',
          identifier: 'designer',
          image: {
           filePath: '/path/designer.jpg',
            originalFilename: 'designer.jpg',
          },
          labels: {
            en_US: 'Designer',
            fr_FR: 'Designer',
          },
        },
        values: [
            {
              attribute: {
                code: 'name',
                identifier: 'name_designer_fingerprint',
                is_required: false,
                is_read_only: false,
                is_rich_text_editor: false,
                is_textarea: false,
                labels: {fr_FR: 'Nom'},
                max_length: 25,
                order: 0,
                asset_family_identifier: 'designer',
                regular_expression: null,
                type: 'text',
                validation_rule: 'none',
                value_per_channel: false,
                value_per_locale: false,
              },
              channel: null,
              data: 'Philippe Starck',
              locale: null,
            },
            {
              attribute: {
                code: 'description',
                identifier: 'description_designer_fingerprint',
                is_required: false,
                is_read_only: false,
                is_rich_text_editor: true,
                is_textarea: true,
                labels: {fr_FR: 'Description'},
                max_length: 25,
                order: 1,
                asset_family_identifier: 'designer',
                regular_expression: null,
                type: 'text',
                validation_rule: 'none',
                value_per_channel: false,
                value_per_locale: true,
              },
              channel: null,
              data: null,
              locale: 'en_US',
            },
            {
              attribute: {
                code: 'description',
                identifier: 'description_designer_fingerprint',
                is_required: false,
                is_read_only: false,
                is_rich_text_editor: true,
                is_textarea: true,
                labels: {fr_FR: 'Description'},
                max_length: 25,
                order: 1,
                asset_family_identifier: 'designer',
                regular_expression: null,
                type: 'text',
                validation_rule: 'none',
                value_per_channel: false,
                value_per_locale: true,
              },
              channel: null,
              data: null,
              locale: 'fr_FR',
            },
            {
              attribute: {
                code: 'website',
                identifier: 'website_designer_fingerprint',
                is_required: true,
                is_read_only: false,
                is_rich_text_editor: false,
                is_textarea: false,
                labels: {fr_FR: 'Website'},
                max_length: 25,
                order: 2,
                asset_family_identifier: 'designer',
                regular_expression: null,
                type: 'text',
                validation_rule: 'url',
                value_per_channel: false,
                value_per_locale: false,
              },
              channel: null,
              data: null,
              locale: null,
            },
            {
              attribute: {
                allowed_extensions: ['png'],
                code: 'portrait',
                identifier: 'portrait_designer_fingerprint',
                is_required: false,
                is_read_only: false,
                labels: {en_US: 'Portrait', fr_FR: 'Image autobiographique'},
                max_file_size: '200.10',
                media_type: 'image',
                order: 3,
                asset_family_identifier: 'designer',
                type: 'media_file',
                value_per_channel: false,
                value_per_locale: false,
              },
              channel: null,
              data: null,
              locale: null,
            },
            {
              attribute: {
                code: 'age',
                asset_family_identifier: 'designer',
                identifier: 'age_designer_fingerprint',
                decimals_allowed: false,
                is_required: false,
                is_read_only: false,
                labels: {en_US: 'Age', fr_FR: 'Age'},
                max_value: '20',
                min_value: '10',
                order: 4,
                type: 'number',
                value_per_channel: false,
                value_per_locale: false,
              },
              channel: null,
              data: null,
              locale: null,
            },
          ],
      },
    });
  });

  it('It search for assets', async () => {
    const requestContract = getRequestContract('Asset/Search/ok.json');

    await listenRequest(page, requestContract);

    const response = await page.evaluate(async () => {
      // Sometimes this test fails on circle ci. This wait should mitigate that
      await new Promise((resolve) => setTimeout(resolve, 500));

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
          assetFamilyIdentifier: 'designer',
          image: [],
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
          assetFamilyIdentifier: 'designer',
          image: [],
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

  // it('It search for empty assets', async () => {
  //   const requestContract = getRequestContract('Asset/Search/no_result.json');
  //
  //   await listenRequest(page, requestContract);
  //
  //   const response = await page.evaluate(async () => {
  //     const fetcher = require('akeneoassetmanager/infrastructure/fetcher/asset').default;
  //
  //     return await fetcher.search({
  //       locale: 'en_US',
  //       channel: 'ecommerce',
  //       size: 200,
  //       page: 0,
  //       filters: [
  //         {
  //           field: 'full_text',
  //           operator: '=',
  //           value: 'search',
  //           context: {},
  //         },
  //         {
  //           field: 'asset_family',
  //           operator: '=',
  //           value: 'designer',
  //           context: {},
  //         },
  //       ],
  //     });
  //   });
  //
  //   expect(response).toEqual({
  //     items: [],
  //     matchesCount: 0,
  //     totalCount: 3,
  //   });
  // });
});
