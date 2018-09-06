import * as _ from 'underscore';
import BaseForm = require('pimenrich/js/view/base');
const __ = require('oro/translator');
const SimpleSelectAttribute = require('pimee/settings/mapping/simple-select-attribute');
const template = require('pimee/template/settings/mapping/attributes-mapping');

/**
 * This module will allow user to map the attributes from PIM.ai to the catalog attributes.
 * It displays a grid with all the attributes to map.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
interface Config {
  labels: {
    pending: string,
    mapped: string,
    unmapped: string,
    pim_ai_attribute: string,
    catalog_attribute: string,
    suggest_data: string
  }
}

/* Defined in Akeneo/Pim/Automation/SuggestData/Infrastructure/Controller/AttributeMappingController.php */
const ATTRIBUTE_PENDING: number = 0;
const ATTRIBUTE_MAPPED: number = 1;
const ATTRIBUTE_UNMAPPED: number = 2;

const VALID_MAPPING: { [key: string]: string[] } = {
  'metric': [ 'pim_catalog_metric' ],
  'select': [ 'pim_catalog_simpleselect' ],
  'multiselect': [ 'pim_catalog_multiselect' ],
  'number': [ 'pim_catalog_number' ],
  'text': [ 'pim_catalog_text' ],
};

class InterfaceNormalizedAttributeMapping {
    mapping: {
      [key: string] : {
        pim_ai_attribute: {
          label: string,
          type: string
        },
        attribute: string,
        status: number
      }
    };
}

class AttributeMapping extends BaseForm {
  readonly template = _.template(template);
  readonly config: Config = {
    labels: {
      pending: '',
      mapped: '',
      unmapped: '',
      pim_ai_attribute: '',
      catalog_attribute: '',
      suggest_data: ''
    }
  };

  /**
   * {@inheritdoc}
   */
  constructor(options: {config: Config}) {
    super(options);

    this.config = {...this.config, ...options.config};
  }

  /**
   * {@inheritdoc}
   */
  render() {
    this.$el.html('');
    const familyMapping: InterfaceNormalizedAttributeMapping = this.getFormData();
    if (familyMapping.hasOwnProperty('mapping') && Object.keys(familyMapping.mapping).length) {
      const mapping = familyMapping.mapping;
      const statuses: { [key: number]: string } = {};
      statuses[ATTRIBUTE_PENDING] = __(this.config.labels.pending);
      statuses[ATTRIBUTE_MAPPED] = __(this.config.labels.mapped);
      statuses[ATTRIBUTE_UNMAPPED] = __(this.config.labels.unmapped);
      this.$el.html(this.template({
        mapping,
        statuses,
        pim_ai_attribute: __(this.config.labels.pim_ai_attribute),
        catalog_attribute: __(this.config.labels.catalog_attribute),
        suggest_data: __(this.config.labels.suggest_data)
      }));
      Object.keys(mapping).forEach((pim_ai_attribute_code) => {
        const $dom = this.$el.find(
          '.attribute-selector[data-pim-ai-attribute-code="' + pim_ai_attribute_code + '"]'
        );
        const attributeSelector = new SimpleSelectAttribute({
          config: {
            /**
             * The normalized managed object looks like:
             * { mapping: {
             *     pim_ai_attribute_code_1: { attribute: 'foo' ... },
             *     pim_ai_attribute_code_2: { attribute: 'bar' ... }
             * } }
             */
            fieldName: 'mapping.' + pim_ai_attribute_code + '.attribute',
            label: '',
            choiceRoute: 'pim_enrich_attribute_rest_index',
            types: VALID_MAPPING[mapping[pim_ai_attribute_code].pim_ai_attribute.type],
          },
          className: 'AknFieldContainer AknFieldContainer--withoutMargin AknFieldContainer--inline'
        });
        attributeSelector.configure().then(() => {
          attributeSelector.setParent(this);
          $dom.html(attributeSelector.render().$el);
        });
      });
    }

    this.renderExtensions();

    return this;
  }
}

export = AttributeMapping
