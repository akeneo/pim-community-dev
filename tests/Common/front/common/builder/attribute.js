/**
 * Generate an attribute
 *
 * Example:
 *
 * const AttributeBuilder = require('../../common/builder/attribute');
 * const attribute = (new AttributeBuilder())
 *   .withCode('name')
 *   .withType('pim_catalog_text')
 *   .withLabels({ en_AU: 'nam' })
 *   .withGroup('marketing')
 *   .build();
 */
class AttributeBuilder {
  constructor() {
    this.attribute = {
      code: 'name',
      type: 'pim_catalog_text',
      group: 'marketing',
      unique: false,
      useable_as_grid_filter: true,
      allowed_extensions: [],
      metric_family: null,
      default_metric_unit: null,
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
        de_DE: 'Name',
        en_US: 'Name',
        fr_FR: 'Nom'
      },
      auto_option_sorting: null,
      is_read_only: false,
      empty_value: '',
      field_type: 'akeneo-text-field',
      filter_types: {
        'product-export-builder': 'akeneo-attribute-text-filter'
      },
      is_locale_specific: false,
      meta: {
        id: 1,
        structure_version: 1523353599,
        model_type: 'attribute'
      }
    };
  }

  withCode(code) {
    this.attribute.code = code;

    return this;
  }

  withType(type) {
    this.attribute.type = type;

    return this;
  }

  withGroup(group) {
    this.attribute.group = group;

    return this;
  }

  withLabels(labels) {
    this.attribute.labels = labels;

    return this;
  }

  build() {
    return this.attribute;
  }
}

module.exports = AttributeBuilder;
