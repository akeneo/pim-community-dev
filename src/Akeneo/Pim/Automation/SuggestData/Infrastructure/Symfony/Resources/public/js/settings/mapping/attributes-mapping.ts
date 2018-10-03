import SimpleSelectAttribute = require('akeneosuggestdata/js/settings/mapping/simple-select-attribute');
import BaseForm = require('pimenrich/js/view/base');
import * as _ from 'underscore';
import {EventsHash} from "backbone";
import BootstrapModal = require('pimui/lib/backbone.bootstrap-modal');

const __ = require('oro/translator');
const FetcherRegistry = require('pim/fetcher-registry');
const FormBuilder = require('pim/form-builder');
const template = require('pimee/template/settings/mapping/attributes-mapping');
const noDataTemplate = require('pim/template/common/no-data');

interface NormalizedAttributeMappingInterface {
  mapping: {
    [key: string]: {
      pim_ai_attribute: {
        label: string,
        type: string,
      },
      attribute: string,
      status: number,
    },
  };
}

interface AttributeMappingConfig {
  labels: {
    pending: string,
    mapped: string,
    unmapped: string,
    pim_ai_attribute: string,
    catalog_attribute: string,
    suggest_data: string, // TODO Rename to attribute_mapping_status
  }
}

/**
 * This module will allow user to map the attributes from PIM.ai to the catalog attributes.
 * It displays a grid with all the attributes to map.
 *
 * The attribute types authorized for the mapping are defined in
 * Akeneo\Pim\Automation\SuggestData\Infrastructure\Controller\AttributeMappingController
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class AttributeMapping extends BaseForm {
  private static readonly ATTRIBUTE_PENDING: number = 0;
  private static readonly ATTRIBUTE_MAPPED: number = 1;
  private static readonly ATTRIBUTE_UNMAPPED: number = 2;
  private static readonly VALID_MAPPING: { [key: string]: string[] } = {
    metric: [ 'pim_catalog_metric' ],
    select: [ 'pim_catalog_simpleselect' ],
    multiselect: [ 'pim_catalog_multiselect' ],
    number: [ 'pim_catalog_number' ],
    text: [ 'pim_catalog_text' ],
  };

  private readonly template = _.template(template);
  private readonly noDataTemplate = _.template(noDataTemplate);
  private readonly config: AttributeMappingConfig = {
    labels: {
      pending: '',
      mapped: '',
      unmapped: '',
      pim_ai_attribute: '',
      catalog_attribute: '',
      suggest_data: '',
    },
  };

  /**
   * {@inheritdoc}
   */
  constructor(options: {config: AttributeMappingConfig}) {
    super(options);

    this.config = {...this.config, ...options.config};
  }

  /**
   * {@inheritdoc}
   */
  public configure(): JQueryPromise<any> {
    return $.when(
      this.onExtensions('pim_datagrid:filter-front', this.filter.bind(this)),
    );
  }

  /**
   * {@inheritdoc}
   */
  public render(): BaseForm {
    this.$el.html('');
    const familyMapping: NormalizedAttributeMappingInterface = this.getFormData();
    const mapping = familyMapping.hasOwnProperty('mapping') ? familyMapping.mapping : {};
    const gridHtml = this.template({
      mapping,
      statuses: this.getMappingStatuses(),
      pim_ai_attribute: __(this.config.labels.pim_ai_attribute),
      catalog_attribute: __(this.config.labels.catalog_attribute),
      suggest_data: __(this.config.labels.suggest_data),
    });
    const noDataHtml = this.noDataTemplate({
      __,
      imageClass: '',
      hint: __('pim_datagrid.no_results', {
        entityHint: __('akeneo_suggest_data.entity.attributes_mapping.fields.pim_ai_attribute'),
      }),
      subHint: 'pim_datagrid.no_results_subtitle',
    });

    this.$el.html(gridHtml + noDataHtml);

    Object.keys(mapping).forEach((pimAiAttributeCode: string) => {
      this.appendAttributeSelector(mapping, pimAiAttributeCode);
    });

    this.toggleAttributeOptionButtons(Object.keys(mapping).reduce((acc, pimAiAttributeCode: string) => {
      acc[pimAiAttributeCode] = mapping[pimAiAttributeCode].attribute;

      return acc;
    }, <{ [key: string]: string }> {}));


    this.toggleNoDataMessage();
    this.renderExtensions();
    this.delegateEvents();

    return this;
  }

  /**
   * {@inheritdoc}
   */
  public events(): EventsHash {
    return {
      'click .option-mapping': this.updateAttributeOptionsMapping,
    }
  }

  /**
   * Filters the rows with a filter.
   * Each row contains a 'data' element called 'active-filters'. This element contains a list of filters. A filter is
   * contained in this row if it is hidden by this filter. The row is displayed if there is no active filters in it,
   * i.e. the active filters are empty.
   *
   * @param {{value: string, type: "equals" | "search", field: string}} filter
   */
  private filter(filter: { value: string, type: 'equals'|'search', field: string }): void {
    this.$el.find('.searchable-row').each((_i: number, row: any) => {
      const value = $(row).data(filter.field);
      let filteredByThisFilter = false;
      switch (filter.type) {
        case 'equals': filteredByThisFilter = !this.filterEquals(filter.value, value); break;
        case 'search': filteredByThisFilter = !this.filterSearch(filter.value, value); break;
      }

      let filters = $(row).data('active-filters');
      if (undefined === filters) {
        filters = [];
      }
      if ((filters.indexOf(filter.field) < 0) && filteredByThisFilter) {
        filters.push(filter.field);
      } else if ((filters.indexOf(filter.field) >= 0) && !filteredByThisFilter) {
        filters.splice(filters.indexOf(filter.field), 1);
      }
      $(row).data('active-filters', filters);

      filters.length > 0 ? $(row).hide() : $(row).show();

      this.toggleNoDataMessage();
    });
  }

  /**
   * Toggle the "there is no data" message regarding the number of visible rows.
   */
  private toggleNoDataMessage(): void {
    this.$el.find('.searchable-row:visible').length ?
      this.$el.find('.no-data-inner').hide() :
      this.$el.find('.no-data-inner').show();
  }

  /**
   * Returns true if the values are the same.
   *
   * @param {string} filterValue
   * @param {string} rowValue
   *
   * @returns {boolean}
   */
  private filterEquals(filterValue: string, rowValue: string): boolean {
    return filterValue === '' || filterValue === rowValue;
  }

  /**
   * Return if the row matches the search filter by words. If the user types 'foo bar', it will look for every row
   * containing the strings 'foo' and 'bar', no matter the order of the words.
   *
   * @param {string} filterValue
   * @param {string} rowValue
   *
   * @returns {boolean}
   */
  private filterSearch(filterValue: string, rowValue: string): boolean {
    const words: string[] = filterValue.split(' ');

    return words.reduce((acc, word) => {
      return acc && rowValue.indexOf(word) >= 0;
    }, true);
  }

  /**
   * @param mapping
   * @param {string} pimAiAttributeCode
   */
  private appendAttributeSelector(mapping: any, pimAiAttributeCode: string) {
    const $dom = this.$el.find(
      '.attribute-selector[data-pim-ai-attribute-code="' + pimAiAttributeCode + '"]'
    );
    const attributeSelector = new SimpleSelectAttribute({
      config: {
        fieldName: 'mapping.' + pimAiAttributeCode + '.attribute',
        label: '',
        choiceRoute: 'pim_enrich_attribute_rest_index',
        types: AttributeMapping.VALID_MAPPING[mapping[pimAiAttributeCode].pim_ai_attribute.type],
      },
      className: 'AknFieldContainer AknFieldContainer--withoutMargin AknFieldContainer--inline'
    });
    attributeSelector.configure().then(() => {
      attributeSelector.setParent(this);
      $dom.html(attributeSelector.render().$el);
    });
  }

  /**
   * @returns {{ [ key: number ]: string }}
   */
  private getMappingStatuses() {
    const statuses: { [key: number]: string } = {};
    statuses[AttributeMapping.ATTRIBUTE_PENDING] = __(this.config.labels.pending);
    statuses[AttributeMapping.ATTRIBUTE_MAPPED] = __(this.config.labels.mapped);
    statuses[AttributeMapping.ATTRIBUTE_UNMAPPED] = __(this.config.labels.unmapped);

    return statuses;
  }

  /**
   * This method will show or hide the Attribute Option buttons.
   * The first parameter is the current mapping, from pimAiAttribute to pimAttribute.
   *
   * @param {{ [key: string]: string | null}} mapping
   */
  private toggleAttributeOptionButtons(mapping: { [key: string]: string | null }) {
    const pimAttributes = Object.values(mapping).filter((pimAttribute) => {
      return '' !== pimAttribute && null !== pimAttribute;
    });

    FetcherRegistry
      .getFetcher('attribute')
      .fetchByIdentifiers(pimAttributes)
      .then((attributes: { code: string, type: string }[]) => {
      Object.keys(mapping).forEach((pimAiAttribute) => {
        const $attributeOptionButton = this.$el.find('.option-mapping[data-pim-ai-attribute-code=' + pimAiAttribute + ']');
        const attribute = attributes.find((attribute: { code: string, type: string }) => {
          return attribute.code === mapping[pimAiAttribute];
        });
        const type = undefined === attribute ? '' : attribute.type;

        ['pim_catalog_simpleselect', 'pim_catalog_multiselect'].indexOf(type) >= 0 ?
          $attributeOptionButton.show() :
          $attributeOptionButton.hide();
      });
    });
  }

  private updateAttributeOptionsMapping(event: any) {
    console.log(event);
    this.manageAttributeOptionsMapping();
    //.then(attributeOptionsMapping => {
    //  console.log(attributeOptionsMapping);
      //this.data = assets;

      //this.trigger('collection:change', assets);
      //this.render();
    //});
  }

  private manageAttributeOptionsMapping() {
    //const deferred = $.Deferred();

    FormBuilder.build('pimee-suggest-data-settings-attribute-options-mapping-edit').then((form: any) => {
      let modal = new BootstrapModal({
        className: 'modal modal--fullPage modal--topButton',
        modalOptions: {
          backdrop: 'static',
          keyboard: false
        },
        allowCancel: true,
        okCloses: false,
        title: '',
        content: '',
        cancelText: ' ',
        okText: __('pim_common.save')
      });
      modal.open();

      form
        .setFamilyLabel('family')
        .setPimAiAttributeLabel('Pim Ai Attribute')
        .setPimAttributeCode('color')
        .setFamilyCode('router')
        .setElement(modal.$('.modal-body'))
        .render();

      //modal.on('cancel', deferred.reject);
      modal.on('ok', () => {
        //const attributeOptions = _.sortBy(form.getItems(), 'code');
        modal.close();

        //deferred.resolve(attributeOptions);
      });
    });

    //return deferred.promise();
  }
}

export = AttributeMapping;
