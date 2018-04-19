const response = [
  {
    code: 'weight',
    type: 'pim_catalog_metric',
    group: 'technical',
    unique: false,
    useable_as_grid_filter: true,
    allowed_extensions: [],
    metric_family: 'Weight',
    default_metric_unit: 'KILOGRAM',
    reference_data_name: null,
    available_locales: [],
    max_characters: null,
    validation_rule: null,
    validation_regexp: null,
    wysiwyg_enabled: null,
    number_min: null,
    number_max: null,
    decimals_allowed: true,
    negative_allowed: false,
    date_min: null,
    date_max: null,
    max_file_size: null,
    minimum_input_length: null,
    sort_order: 1,
    localizable: false,
    scopable: false,
    labels: {
      de_DE: 'Gewicht',
      en_US: 'Weight',
      fr_FR: 'Poids'
    },
    auto_option_sorting: null,
    is_read_only: false,
    empty_value: {
      amount: null,
      unit: 'KILOGRAM'
    },
    field_type: 'akeneo-metric-field',
    filter_types: {
      'product-export-builder': 'akeneo-attribute-metric-filter'
    },
    is_locale_specific: false,
    meta: {
      id: 7,
      structure_version: 1523353599,
      model_type: 'attribute'
    }
  }
];

describe('Pimenrich > fetcher > family', () => {
  let page = global.__PAGE__;


  it('provide an empty list of axis', async () => {
    page.once('request', request => {
      if (request.url().includes('/configuration/family/rest/camcorder/available_axes')) {
        request.respond({
          contentType: 'application/json',
          body: '[]',
        });
      }
    });

    const axis = await page.evaluate(async () => {
      const fetcherRegistry = require('pim/fetcher-registry');

      return await fetcherRegistry
        .getFetcher('family')
        .fetchAvailableAxes('camcorder');
    });

    expect(axis).toEqual([]);
  });

  it('provide an non empty list of axis', async () => {
    page.once('request', request => {
      if (request.url().includes('/configuration/family/rest/camcorder/available_axes')) {
        request.respond({
          contentType: 'application/json',
          body: JSON.stringify(response),
        });
      }
    });

    const axis = await page.evaluate(async () => {
      const fetcherRegistry = require('pim/fetcher-registry');

      return await fetcherRegistry
        .getFetcher('family')
        .fetchAvailableAxes('camcorder');
    });

    expect(axis).toEqual(response);
  });
});
