import Filterable = require('akeneosuggestdata/js/common/filterable');
import SimpleSelectAttribute = require('akeneosuggestdata/js/settings/mapping/simple-select-attribute');
import {EventsHash} from 'backbone';
import * as $ from 'jquery';
import BaseForm = require('pimenrich/js/view/base');
import BootstrapModal = require('pimui/lib/backbone.bootstrap-modal');
import * as _ from 'underscore';
import AttributeOptionsMapping = require('./attribute-options-mapping');

const __ = require('oro/translator');
const FetcherRegistry = require('pim/fetcher-registry');
const FormBuilder = require('pim/form-builder');
const Router = require('pim/router');
const template = require('pimee/template/settings/mapping/attributes-mapping');
const i18n = require('pim/i18n');
const UserContext = require('pim/user-context');

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

interface Config {
  labels: {
    pending: string,
    mapped: string,
    unmapped: string,
    pim_ai_attribute: string,
    catalog_attribute: string,
    suggest_data: string, // TODO Rename to attribute_mapping_status
  };
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
  private readonly config: Config = {
    labels: {
      pending: '',
      mapped: '',
      unmapped: '',
      pim_ai_attribute: '',
      catalog_attribute: '',
      suggest_data: '',
    },
  };
  private attributeOptionsMappingModal: any = null;
  private attributeOptionsMappingForm: BaseForm | null = null;

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
  public configure(): JQueryPromise<any> {
    Filterable.set(this);

    return BaseForm.prototype.configure.apply(this, arguments);
  }

  /**
   * {@inheritdoc}
   */
  public render(): BaseForm {
    this.$el.html('');
    const familyMapping: NormalizedAttributeMappingInterface = this.getFormData();
    const mapping = familyMapping.hasOwnProperty('mapping') ? familyMapping.mapping : {};
    this.$el.html(this.template({
      mapping,
      statuses: this.getMappingStatuses(),
      pim_ai_attribute: __(this.config.labels.pim_ai_attribute),
      catalog_attribute: __(this.config.labels.catalog_attribute),
      suggest_data: __(this.config.labels.suggest_data),
    }));

    Object.keys(mapping).forEach((pimAiAttributeCode: string) => {
      this.appendAttributeSelector(mapping, pimAiAttributeCode);
    });

    this.toggleAttributeOptionButtons(Object.keys(mapping).reduce((acc, pimAiAttributeCode: string) => {
      acc[pimAiAttributeCode] = mapping[pimAiAttributeCode].attribute;

      return acc;
    }, {} as { [key: string]: string }));

    Filterable.afterRender(this, __('akeneo_suggest_data.entity.attributes_mapping.fields.pim_ai_attribute'));

    this.renderExtensions();
    this.delegateEvents();

    return this;
  }

  /**
   * {@inheritdoc}
   */
  public events(): EventsHash {
    return {
      'click .option-mapping': this.openAttributeOptionsMappingModal,
    };
  }

  /**
   * @param mapping
   * @param {string} pimAiAttributeCode
   */
  private appendAttributeSelector(mapping: any, pimAiAttributeCode: string) {
    const $dom = this.$el.find(
      '.attribute-selector[data-pim-ai-attribute-code="' + pimAiAttributeCode + '"]',
    );
    const attributeSelector = new SimpleSelectAttribute({
      config: {
        fieldName: 'mapping.' + pimAiAttributeCode + '.attribute',
        label: '',
        choiceRoute: 'pim_enrich_attribute_rest_index',
        types: AttributeMapping.VALID_MAPPING[mapping[pimAiAttributeCode].pim_ai_attribute.type],
      },
      className: 'AknFieldContainer AknFieldContainer--withoutMargin AknFieldContainer--inline',
    });
    attributeSelector.configure().then(() => {
      attributeSelector.setParent(this);
      $dom.html(attributeSelector.render().$el);
    });
  }

  /**
   * @returns { [ key: number ]: string }
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
   * The first parameter is the current mapping, from pimAiAttributeCode to pimAttributeCode.
   *
   * @param { [pimAiAttributeCode: string]: string | null } mapping
   */
  private toggleAttributeOptionButtons(mapping: { [pimAiAttributeCode: string]: string | null }) {
    const pimAttributes = Object.values(mapping).filter((pimAttribute) => {
      return '' !== pimAttribute && null !== pimAttribute;
    });

    FetcherRegistry
      .getFetcher('attribute')
      .fetchByIdentifiers(pimAttributes)
      .then((attributes: Array<{ code: string, type: string }>) => {
      Object.keys(mapping).forEach((pimAiAttribute) => {
        const $attributeOptionButton = this.$el.find(
          '.option-mapping[data-pim-ai-attribute-code=' + pimAiAttribute + ']',
        );
        const attribute = attributes.find((attr: { code: string, type: string }) => {
          return attribute.code === mapping[pimAiAttribute];
        });
        const type = undefined === attr ? '' : attr.type;

        ['pim_catalog_simpleselect', 'pim_catalog_multiselect'].indexOf(type) >= 0 ?
          $attributeOptionButton.show() :
          $attributeOptionButton.hide();
      });
    });
  }

  /**
   * Open the modal for the attribute options mapping
   *
   * @param { currentTarget: any } event
   */
  private openAttributeOptionsMappingModal(event: { currentTarget: any }) {
    const $line = $(event.currentTarget).closest('.line');
    const pimAiAttributeLabel = $line.data('pim_ai_attribute');
    const pimAiAttributeCode = $line.find('.attribute-selector').data('pim-ai-attribute-code');
    const catalogAttributeCode =
        $line.find('input[name="mapping.' + pimAiAttributeCode + '.attribute"]').val() as string;

    const familyCode = Router.match(window.location.hash).params.familyCode;
    $.when(
      FormBuilder.build('pimee-suggest-data-settings-attribute-options-mapping-edit'),
      FetcherRegistry.getFetcher('family').fetch(familyCode),
    ).then((
      form: BaseForm,
      normalizedFamily: any,
    ) => {
      this.attributeOptionsMappingModal = new BootstrapModal({
        className: 'modal modal--fullPage modal--topButton',
        modalOptions: {
          backdrop: 'static',
          keyboard: false,
        },
        allowCancel: true,
        okCloses: false,
        title: '',
        content: '',
        cancelText: ' ',
        okText: __('pim_common.save'),
      });
      this.attributeOptionsMappingModal.open();
      this.attributeOptionsMappingForm = form;

      const formContent = form.getExtension('content') as AttributeOptionsMapping;
      formContent
        .setFamilyLabel(i18n.getLabel(normalizedFamily.labels, UserContext.get('catalogLocale'), normalizedFamily.code))
        .setPimAiAttributeLabel(pimAiAttributeLabel)
        .setPimAttributeCode(catalogAttributeCode)
        .setFamilyCode(familyCode);

      this.listenTo(form, 'pim_enrich:form:entity:post_save', this.closeAttributeOptionsMappingModal.bind(this));

      $('.modal .ok').replaceWith(this.getRoot().$el.find('*[data-drop-zone="buttons"]'));

      form.setElement(this.attributeOptionsMappingModal.$('.modal-body')).render();

      this.attributeOptionsMappingModal.on('cancel', this.closeAttributeOptionsMappingModal);
    });
  }

  /**
   * Closes the modal then destroy all its data inside.
   */
  private closeAttributeOptionsMappingModal(): void {
    if (null !== this.attributeOptionsMappingModal) {
      this.attributeOptionsMappingModal.close();
      this.attributeOptionsMappingModal = null;
    }

    if (null !== this.attributeOptionsMappingForm) {
      this.attributeOptionsMappingForm.shutdown();
      this.attributeOptionsMappingForm = null;
    }
  }
}

export = AttributeMapping;
