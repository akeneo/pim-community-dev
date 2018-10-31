/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {EventsHash} from 'backbone';
import * as $ from 'jquery';
import BaseForm = require('pimui/js/view/base');
import BootstrapModal = require('pimui/lib/backbone.bootstrap-modal');
import * as _ from 'underscore';
import {EscapeHtml} from '../../common/escape-html';
import {Filterable} from '../../common/filterable';
import AttributeOptionsMapping = require('../attribute-options-mapping/edit');
import SimpleSelectAttribute = require('../common/simple-select-attribute');
const __ = require('oro/translator');
const FetcherRegistry = require('pim/fetcher-registry');
const FormBuilder = require('pim/form-builder');
const Router = require('pim/router');
const template = require('pimee/template/attributes-mapping/table');
const i18n = require('pim/i18n');
const UserContext = require('pim/user-context');

interface NormalizedAttributeMapping {
  mapping: {
    [franklinAttribute: string]: {
      pimAiAttribute: {
        label: string,
        type: string,
        summary: string[],
      },
      attribute: string,
      status: number,
    },
  };
}

interface NormalizedAttribute {
  code: string;
  type: string;
}

interface Config {
  labels: {
    pending: string,
    mapped: string,
    unmapped: string,
    pimAiAttribute: string,
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
 * The attribute types authorized for the mapping are defined in
 * Akeneo\Pim\Automation\SuggestData\Infrastructure\Controller\AttributeMappingController
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class AttributeMapping extends BaseForm {
  private static readonly ATTRIBUTE_PENDING: number = 0;
  private static readonly ATTRIBUTE_MAPPED: number = 1;
  private static readonly ATTRIBUTE_UNMAPPED: number = 2;
  private static readonly VALID_MAPPING: { [attributeType: string]: string[] } = {
    metric: [ 'pim_catalog_metric' ],
    select: [ 'pim_catalog_simpleselect' ],
    multiselect: [ 'pim_catalog_multiselect' ],
    number: [ 'pim_catalog_number' ],
    text: [ 'pim_catalog_text' ],
  };
  private static readonly ATTRIBUTE_TYPES_BUTTONS_VISIBILITY = ['pim_catalog_simpleselect', 'pim_catalog_multiselect'];

  private readonly template = _.template(template);
  private readonly config: Config = {
    labels: {
      pending: '',
      mapped: '',
      unmapped: '',
      pimAiAttribute: '',
      catalogAttribute: '',
      attributeMappingStatus: '',
      valuesSummary: '',
      type: '',
    },
  };
  private attributeOptionsMappingModal: any = null;
  private attributeOptionsMappingForm: BaseForm | null = null;
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

    return BaseForm.prototype.configure.apply(this, arguments);
  }

  /**
   * {@inheritdoc}
   */
  public render(): BaseForm {
    const familyMapping: NormalizedAttributeMapping = this.getFormData();
    const mapping = familyMapping.hasOwnProperty('mapping') ? familyMapping.mapping : {};
    this.$el.html(this.template({
      __,
      mapping,
      escapeHtml: EscapeHtml.escapeHtml,
      statuses: this.getMappingStatuses(),
      pimAiAttribute: __(this.config.labels.pimAiAttribute),
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

        Filterable.afterRender(this, __(this.config.labels.pimAiAttribute));

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
    const attributeSelector = new SimpleSelectAttribute({
      config: {
        fieldName: 'mapping.' + franklinAttributeCode + '.attribute',
        label: '',
        choiceRoute: 'pim_enrich_attribute_rest_index',
        types: AttributeMapping.VALID_MAPPING[mapping[franklinAttributeCode].pimAiAttribute.type],
      },
      className: 'AknFieldContainer AknFieldContainer--withoutMargin AknFieldContainer--inline',
    });
    attributeSelector.configure().then(() => {
      attributeSelector.setParent(this);
      attributeSelector.render();
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
  }

  /**
   * @returns { [ status: number ]: string }
   */
  private getMappingStatuses() {
    const statuses: { [status: number]: string } = {};
    statuses[AttributeMapping.ATTRIBUTE_PENDING] = __(this.config.labels.pending);
    statuses[AttributeMapping.ATTRIBUTE_MAPPED] = __(this.config.labels.mapped);
    statuses[AttributeMapping.ATTRIBUTE_UNMAPPED] = __(this.config.labels.unmapped);

    return statuses;
  }

  /**
   * Open the modal for the attribute options mapping
   *
   * @param { { currentTarget: any } } event
   */
  private openAttributeOptionsMappingModal(event: { currentTarget: any }) {
    const $line = $(event.currentTarget).closest('.line');
    const franklinAttributeLabel = $line.data('pim-ai-attribute') as string;
    const franklinAttributeCode = $line.find('.attribute-selector').data('franklin-attribute-code');
    const catalogAttributeCode =
        $line.find('input[name="mapping.' + franklinAttributeCode + '.attribute"]').val() as string;
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
      });
      this.attributeOptionsMappingModal.open();
      this.attributeOptionsMappingForm = form;

      const formContent = form.getExtension('content') as AttributeOptionsMapping;
      formContent
        .setFamilyLabel(i18n.getLabel(normalizedFamily.labels, UserContext.get('catalogLocale'), normalizedFamily.code))
        .setFamilyCode(familyCode)
        .setFranklinAttributeLabel(franklinAttributeLabel)
        .setCatalogAttributeCode(catalogAttributeCode);

      this.listenTo(form, 'pim_enrich:form:entity:post_save', this.closeAttributeOptionsMappingModal.bind(this));

      $('.modal .ok').remove();
      form.setElement(this.attributeOptionsMappingModal.$('.modal-body')).render();

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
