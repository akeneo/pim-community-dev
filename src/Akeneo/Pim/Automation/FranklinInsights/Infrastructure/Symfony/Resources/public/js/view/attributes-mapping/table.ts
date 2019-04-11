/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {EventsHash} from 'backbone';
import * as Backbone from 'backbone';
import * as $ from 'jquery';
import NormalizedAttribute from 'pim/model/attribute';
import BaseView = require('pimui/js/view/base');
import * as _ from 'underscore';
import {EscapeHtml} from '../../common/escape-html';
import {Filterable} from '../../common/filterable';
import AttributesMappingForFamily from '../../model/attributes-mapping-for-family';
import AttributeOptionsMapping = require('../attribute-options-mapping/edit');
import SimpleSelectAttributeWithWarning = require('./simple-select-attribute-with-warning');

const __ = require('oro/translator');
const FetcherRegistry = require('pim/fetcher-registry');
const FormBuilder = require('pim/form-builder');
const Router = require('pim/router');
const template = require('akeneo/franklin-insights/template/settings/attributes-mapping/table');
const modalTemplate = require('pim/template/common/modal-centered');
const i18n = require('pim/i18n');
const UserContext = require('pim/user-context');

interface Config {
  labels: {
    pending: string,
    active: string,
    inactive: string,
    franklinAttribute: string,
    catalogAttribute: string,
    attributeMappingStatus: string,
    valuesSummary: string,
    type: string,
  };
}

/**
 * This module will allow user to map the attributes from Franklin to the catalog attributes.
 * It displays a grid with all the attributes to map.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class AttributeMapping extends BaseView {
  /** Defined in Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\AttributeMappingStatus */
  public static readonly ATTRIBUTE_PENDING: number = 0;
  public static readonly ATTRIBUTE_ACTIVE: number = 1;
  public static readonly ATTRIBUTE_INACTIVE: number = 2;

  /** Defined in Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Write\AttributeMapping */
  private static readonly PERFECT_MAPPINGS: { [attributeType: string]: string[] } = {
    metric: [ 'pim_catalog_text', 'pim_catalog_textarea', 'pim_catalog_metric' ],
    select: [ 'pim_catalog_text', 'pim_catalog_textarea', 'pim_catalog_simpleselect', 'pim_catalog_multiselect' ],
    multiselect: [ 'pim_catalog_text', 'pim_catalog_textarea', 'pim_catalog_multiselect', 'pim_catalog_simpleselect' ],
    number: [ 'pim_catalog_text', 'pim_catalog_textarea', 'pim_catalog_number' ],
    text: [ 'pim_catalog_text', 'pim_catalog_textarea' ],
  };
  private static readonly ALLOWED_CATALOG_TYPES: string[] = [
    'pim_catalog_metric',
    'pim_catalog_simpleselect',
    'pim_catalog_multiselect',
    'pim_catalog_number',
    'pim_catalog_text',
    'pim_catalog_textarea',
    'pim_catalog_boolean',
  ];
  private static readonly ATTRIBUTE_TYPES_BUTTONS_VISIBILITY = ['pim_catalog_simpleselect', 'pim_catalog_multiselect'];

  /**
   * Get the family code from current URL
   */
  private static getFamilyCode() {
    return Router.match(window.location.hash).params.familyCode;
  }

  private readonly template = _.template(template);
  private readonly modalTemplate = _.template(modalTemplate);
  private readonly config: Config = {
    labels: {
      pending: '',
      active: '',
      inactive: '',
      franklinAttribute: '',
      catalogAttribute: '',
      attributeMappingStatus: '',
      valuesSummary: '',
      type: '',
    },
  };
  private attributeOptionsMappingModal: any = null;
  private attributeOptionsMappingForm: BaseView | null = null;
  private scroll: number = 0;

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

    this.listenTo(this.getRoot(), 'pim_enrich:form:render:before', this.saveScroll);
    this.listenTo(this.getRoot(), 'pim_enrich:form:render:after', this.setScroll);

    return BaseView.prototype.configure.apply(this, arguments);
  }

  /**
   * {@inheritdoc}
   */
  public render(): BaseView {
    const familyMapping: AttributesMappingForFamily = this.getFormData();
    const mapping = familyMapping.hasOwnProperty('mapping') ? familyMapping.mapping : {};

    this.$el.html(this.template({
      __,
      mapping,
      escapeHtml: EscapeHtml.escapeHtml,
      statuses: this.getMappingStatuses(),
      franklinAttribute: __(this.config.labels.franklinAttribute),
      catalogAttribute: __(this.config.labels.catalogAttribute),
      attributeMappingStatus: __(this.config.labels.attributeMappingStatus),
      type: __(this.config.labels.type),
      valuesSummaryKey: this.config.labels.valuesSummary,
    }));

    const catalogAttributes = Object.keys(mapping).reduce((acc, franklinAttributeCode: string) => {
      const catalogAttribute = mapping[franklinAttributeCode].attribute;
      if ('' !== catalogAttribute && null !== catalogAttribute) {
        acc.push(catalogAttribute);
      }

      return acc;
    }, [] as string[]);

    FetcherRegistry
      .getFetcher('attribute')
      .fetchByIdentifiers(catalogAttributes)
      .then((attributes: NormalizedAttribute[]) => {
        Object.keys(mapping).forEach((franklinAttributeCode: string) => {
          const attribute: NormalizedAttribute | undefined = attributes
            .find((attr: NormalizedAttribute) => {
                return attr.code === mapping[franklinAttributeCode].attribute;
              },
            );
          const type = undefined === attribute ? '' : attribute.type;
          const isAttributeOptionsButtonVisible =
            AttributeMapping.ATTRIBUTE_TYPES_BUTTONS_VISIBILITY.indexOf(type) >= 0;

          this.appendAttributeSelector(mapping, franklinAttributeCode, isAttributeOptionsButtonVisible);
        });

        Filterable.afterRender(this, __(this.config.labels.franklinAttribute));

        this.renderExtensions();
        this.delegateEvents();
        this.setScroll();
      });

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
   * @param {string} franklinAttributeCode
   * @param {boolean} isAttributeOptionsButtonVisible
   */
  private appendAttributeSelector(
    mapping: any,
    franklinAttributeCode: string,
    isAttributeOptionsButtonVisible: boolean,
  ) {
    const $dom = this.$el.find(
      '.attribute-selector[data-franklin-attribute-code="' + franklinAttributeCode + '"]',
    );
    const attributeSelector = new SimpleSelectAttributeWithWarning({
      config: {
        fieldName: 'mapping.' + franklinAttributeCode + '.attribute',
        label: '',
        choiceRoute: 'pim_enrich_attribute_rest_index',
        types: AttributeMapping.ALLOWED_CATALOG_TYPES,
        perfectMappings: AttributeMapping.PERFECT_MAPPINGS[mapping[franklinAttributeCode].franklinAttribute.type],
        families: [AttributeMapping.getFamilyCode()],
      },
      className: 'AknFieldContainer AknFieldContainer--withoutMargin AknFieldContainer--inline',
    });
    attributeSelector.configure().then(() => {
      attributeSelector.setParent(this);

      this.deferredRender(attributeSelector).then(() => {
        if (isAttributeOptionsButtonVisible) {
          attributeSelector.$el.find('.icons-container').append(
            $('<div>')
              .addClass('AknIconButton AknIconButton--small AknIconButton--edit AknGrid-onHoverElement option-mapping')
              .attr('data-franklin-attribute-code', franklinAttributeCode)
              .attr('title', __('pim_common.edit')),
          );
        }
        $dom.prepend(attributeSelector.$el);
      });
    });
  }

  /**
   * Async call is done during the render but it does not return promise. So we create one because if we have to append
   * the attribute options mapping button, we need the template to be rendered.
   *
   * @param attributeSelector
   *
   * @returns {JQueryPromise<any>}
   */
  private deferredRender(attributeSelector: SimpleSelectAttributeWithWarning): JQueryPromise<any> {

    return $.Deferred()
      .resolve(
        attributeSelector.render()
      )
      .promise();
  }

  /**
   * @returns { [ status: number ]: string }
   */
  private getMappingStatuses(): { [ status: number ]: string } {
    const statuses: { [status: number]: string } = {};
    statuses[AttributeMapping.ATTRIBUTE_PENDING] = __(this.config.labels.pending);
    statuses[AttributeMapping.ATTRIBUTE_ACTIVE] = __(this.config.labels.active);
    statuses[AttributeMapping.ATTRIBUTE_INACTIVE] = __(this.config.labels.inactive);

    return statuses;
  }

  /**
   * Open the modal for the attribute options mapping
   *
   * @param { { currentTarget: any } } event
   */
  private openAttributeOptionsMappingModal(event: { currentTarget: any }) {
    const $line = $(event.currentTarget).closest('.line');
    const franklinAttributeLabel = $line.data('franklin-attribute') as string;
    const franklinAttributeCode = $line.find('.attribute-selector').data('franklin-attribute-code');
    const catalogAttributeCode =
        $line.find('input[name="mapping.' + franklinAttributeCode + '.attribute"]').val() as string;
    const familyCode = AttributeMapping.getFamilyCode();

    $.when(
      FormBuilder.build('akeneo-franklin-insights-settings-attribute-options-mapping-edit'),
      FetcherRegistry.getFetcher('family').fetch(familyCode),
    ).then((
      form: BaseView,
      normalizedFamily: any,
    ) => {
      const familyLabel = i18n.getLabel(
        normalizedFamily.labels,
        UserContext.get('catalogLocale'),
        normalizedFamily.code,
      );

      const formContent = form.getExtension('content') as AttributeOptionsMapping;
      formContent
        .setFamilyCode(familyCode)
        .setCatalogAttributeCode(catalogAttributeCode)
        .setFranklinAttributeCode(franklinAttributeCode);

      this.attributeOptionsMappingModal = new (Backbone as any).BootstrapModal({
        modalOptions: {
          backdrop: 'static',
          keyboard: false,
        },
        okCloses: false,
        title: __('akeneo_franklin_insights.entity.attribute_options_mapping.module.edit.title', {
          familyLabel,
          franklinAttributeLabel,
        }),
        content: form,
        template: this.modalTemplate,
        innerClassName: 'AknFullPage--full AknFullPage--fixedWidth',
        okText: '',
      });
      this.attributeOptionsMappingModal.open();
      this.attributeOptionsMappingForm = form;

      this.listenTo(form, 'pim_enrich:form:entity:post_save', this.closeAttributeOptionsMappingModal.bind(this));

      this.attributeOptionsMappingModal.on('cancel', this.closeAttributeOptionsMappingModal.bind(this));
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
      this.attributeOptionsMappingForm.getFormModel().clear();
      this.attributeOptionsMappingForm = null;
    }
  }

  /**
   * Saves the scroll top position before the page re-rendering
   */
  private saveScroll(): void {
    this.scroll = $('.edit-form').scrollTop() as number;
  }

  /**
   * Puts back the scroll top position after the render.
   */
  private setScroll(): void {
    $('.edit-form').scrollTop(this.scroll);
  }
}

export = AttributeMapping;
