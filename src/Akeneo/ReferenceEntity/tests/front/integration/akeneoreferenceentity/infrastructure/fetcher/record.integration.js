const timeout = 5000;
const {getRequestContract, listenRequest} = require('../../../../acceptance/cucumber/tools');

describe('Akeneoreferenceentity > infrastructure > fetcher > record', () => {
  let page = global.__PAGE__;

  beforeEach(async () => {
    await page.reload();
  }, timeout);

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
          values: {},
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
              'attribute': 'description_designer_29aea250-bc94-49b2-8259-bbc116410eb2',
              'channel': 'ecommerce',
              'data': 'an awesome designer!',
              'locale': 'en_US'
            },
          },
          completeness: {
            complete: 1,
            required: 1,
          },
        },
      ],
      total: 2,
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
      total: 0,
    });
  });
});
