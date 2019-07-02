/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as Backbone from 'backbone';
import {EventsHash} from 'backbone';
import * as $ from 'jquery';
import {ajax} from 'jquery';
import * as _ from 'underscore';

import {EscapeHtml} from '../../common/escape-html';
import {Filterable} from '../../common/filterable';

import NormalizedAttribute from 'pim/model/attribute';
import IAttributeMapping from '../../model/attribute-mapping';
import AttributeMappingStatus from '../../model/attribute-mapping-status';
import IAttributesMapping from '../../model/attributes-mapping';
import IAttributesMappingForFamily from '../../model/attributes-mapping-for-family';

import BaseView = require('pimui/js/view/base');
import AttributeOptionsMapping = require('../attribute-options-mapping/edit');
import SimpleSelectAttribute from '../common/simple-select-attribute';
import AttributeTypeMismatchWarning from './attribute-type-mismatch-warning';
import CreateAttributeButton from './create-attribute-button';
import AddAttributeToFamilyButton from './add-attribute-to-family-button';

const __ = require('oro/translator');
const FetcherRegistry = require('pim/fetcher-registry');
const FormBuilder = require('pim/form-builder');
const Router = require('pim/router');
const template = require('akeneo/franklin-insights/template/settings/attributes-mapping/table');
const modalTemplate = require('pim/template/common/modal-centered');
const i18n = require('pim/i18n');
const UserContext = require('pim/user-context');
const Property = require('pim/common/property');
const Messenger = require('oro/messenger');
const Routing = require('routing');

interface Config {
  labels: {
    pending: string;
    active: string;
    inactive: string;
    franklinAttribute: string;
    catalogAttribute: string;
  };
}

/** Defined in Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Write\AttributeMapping */
const PERFECT_MAPPINGS: {
  [attributeType: string]: string[];
} = {
  metric: ['pim_catalog_text', 'pim_catalog_textarea', 'pim_catalog_metric'],
  select: ['pim_catalog_text', 'pim_catalog_textarea', 'pim_catalog_simpleselect', 'pim_catalog_multiselect'],
  multiselect: ['pim_catalog_text', 'pim_catalog_textarea', 'pim_catalog_multiselect', 'pim_catalog_simpleselect'],
  number: ['pim_catalog_text', 'pim_catalog_textarea', 'pim_catalog_number'],
  text: ['pim_catalog_text', 'pim_catalog_textarea']
};

const ALLOWED_CATALOG_TYPES: string[] = [
  'pim_catalog_metric',
  'pim_catalog_simpleselect',
  'pim_catalog_multiselect',
  'pim_catalog_number',
  'pim_catalog_text',
  'pim_catalog_textarea',
  'pim_catalog_boolean'
];

const ATTRIBUTE_TYPES_BUTTONS_VISIBILITY = ['pim_catalog_simpleselect', 'pim_catalog_multiselect'];

/**
 * This module will allow user to map the attributes from Franklin to the catalog attributes.
 * It displays a grid with all the attributes to map.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class AttributeMapping extends BaseView {
  private readonly template = _.template(template);
  private readonly modalTemplate = _.template(modalTemplate);
  private readonly config: Config = {
    labels: {
      pending: '',
      active: '',
      inactive: '',
      franklinAttribute: '',
      catalogAttribute: ''
    }
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
  public events(): EventsHash {
    return {
      'click .option-mapping': this.openAttributeOptionsMappingModal,
      'click .deactivate-franklin-attribute': (event: any) =>
        this.deactivateFranklinAttribute(event.target.dataset.franklinAttributeCode)
    };
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
    const familyMapping: IAttributesMappingForFamily = this.getFormData();
    const attributesMapping: IAttributesMapping = familyMapping.hasOwnProperty('mapping') ? familyMapping.mapping : {};

    this.$el.html(
      this.template({
        __,
        mapping: attributesMapping,
        escapeHtml: EscapeHtml.escapeHtml,
        franklinAttribute: __(this.config.labels.franklinAttribute),
        catalogAttribute: __(this.config.labels.catalogAttribute),
        isFranklinAttributeDeactivable: this.isFranklinAttributeDeactivable.bind(this)
      })
    );

    FetcherRegistry.getFetcher('attribute')
      .fetchByIdentifiers(this.getCatalogAttributeCodesFromAttibutesMapping(attributesMapping))
      .then((attributes: NormalizedAttribute[]) => {
        let hasDuplicatedMappedAttribute = false;
        Object.keys(attributesMapping).forEach((franklinAttributeCode: string) => {
          const attributeMapping: IAttributeMapping = attributesMapping[franklinAttributeCode];

          const attribute: NormalizedAttribute | undefined = attributes.find((attr: NormalizedAttribute) => {
            return attr.code === attributeMapping.attribute;
          });
          const type = undefined === attribute ? '' : attribute.type;
          const isAttributeOptionsButtonVisible = ATTRIBUTE_TYPES_BUTTONS_VISIBILITY.indexOf(type) >= 0;

          const isDuplicatedAttribute = this.isPimAttributeAlreadyMapped(attributeMapping.attribute);
          if (true === isDuplicatedAttribute) {
            hasDuplicatedMappedAttribute = true;
          }

          this.appendAttributeSelector(
            attributesMapping,
            franklinAttributeCode,
            isAttributeOptionsButtonVisible,
            isDuplicatedAttribute
          );

          if (true === attributeMapping.canCreateAttribute) {
            const createAttributeButton = this.appendCreateAttributeButton(
              franklinAttributeCode,
              familyMapping.code,
              attributeMapping.franklinAttribute.label,
              attributeMapping.franklinAttribute.type
            );
            createAttributeButton.on('attribute_created', (catalogAttributeCode: string) => {
              createAttributeButton.remove();

              this.suggestAttributeMapping(franklinAttributeCode, catalogAttributeCode);
              this.render();
            });
          } else if (null !== attributeMapping.exactMatchAttributeFromOtherFamily) {
            const addAttributeToFamilyButton = this.appendAddAttributeToFamilyButton(
              attributeMapping.exactMatchAttributeFromOtherFamily,
              familyMapping.code
            );

            addAttributeToFamilyButton.on('attribute_added_to_family', (catalogAttributeCode: string) => {
              attributeMapping.exactMatchAttributeFromOtherFamily = null;
              addAttributeToFamilyButton.remove();

              this.suggestAttributeMapping(franklinAttributeCode, catalogAttributeCode);
              this.render();
            });
          }

          if (type !== '' && false === this.isTypeMappingValid(attributeMapping.franklinAttribute.type, type)) {
            this.appendAttributeTypeMismatchWarning(franklinAttributeCode);
          }
        });
        if (false !== hasDuplicatedMappedAttribute) {
          Messenger.notify(
            'error',
            __('akeneo_franklin_insights.entity.attributes_mapping.constraint.duplicated_pim_attribute')
          );
        }

        Filterable.afterRender(this, __(this.config.labels.franklinAttribute));

        this.renderExtensions();
        this.delegateEvents();
        this.setScroll();
      });

    return this;
  }

  private isPimAttributeAlreadyMapped(pimAttributeCode: string | null): boolean {
    if (null === pimAttributeCode || '' === pimAttributeCode) {
      return false;
    }

    const familyMapping = this.getFormData() as IAttributesMappingForFamily;

    const duplicatedPimAttributeCount = Object.values(familyMapping.mapping).filter(
      (attributeMapping: IAttributeMapping) => {
        return attributeMapping.attribute === pimAttributeCode;
      }
    ).length;

    return duplicatedPimAttributeCount > 1;
  }

  private getCatalogAttributeCodesFromAttibutesMapping(attributesMapping: IAttributesMapping) {
    return Object.keys(attributesMapping).reduce((acc: string[], franklinAttributeCode: string) => {
      const catalogAttribute = attributesMapping[franklinAttributeCode].attribute;
      if ('' !== catalogAttribute && null !== catalogAttribute) {
        acc.push(catalogAttribute);
      }

      return acc;
    }, []);
  }

  private suggestAttributeMapping(franklinAttributeCode: string, catalogAttributeCode: string): void {
    const familyMapping = this.getFormData() as IAttributesMappingForFamily;

    const alreadyMappedAttributeMapping = Object.values(familyMapping.mapping).find(
      mapping => mapping.attribute === catalogAttributeCode
    );

    if (undefined !== alreadyMappedAttributeMapping) {
      Messenger.notify(
        'warning',
        __('akeneo_franklin_insights.entity.attributes_mapping.flash.suggest_attribute_mapping_error', {
          catalogAttributeCode,
          franklinAttributeLabel: alreadyMappedAttributeMapping.franklinAttribute.label
        })
      );

      return;
    }

    familyMapping.mapping[franklinAttributeCode].attribute = catalogAttributeCode;

    this.setData(familyMapping);
  }

  private appendAttributeSelector(
    mapping: IAttributesMapping,
    franklinAttributeCode: string,
    isAttributeOptionsButtonVisible: boolean,
    duplicateMappedAttribute: boolean
  ) {
    let perfectMatchClass = '';
    if (null !== mapping[franklinAttributeCode].attribute && '' !== mapping[franklinAttributeCode].attribute) {
      perfectMatchClass = 'perfect-match';
    }

    let duplicatedErrorClass = '';
    if (true === duplicateMappedAttribute) {
      duplicatedErrorClass = 'error';
    }

    const $dom = this.$el.find('.attribute-selector[data-franklin-attribute-code="' + franklinAttributeCode + '"]');
    const attributeSelector = new SimpleSelectAttribute({
      config: {
        fieldName: Property.propertyPath(['mapping', franklinAttributeCode, 'attribute']),
        label: '',
        choiceRoute: 'pim_enrich_attribute_rest_index',
        types: ALLOWED_CATALOG_TYPES,
        families: [this.getFamilyCode()]
      },
      className: `AknFieldContainer AknFieldContainer--withoutMargin AknFieldContainer--inline ${perfectMatchClass} ${duplicatedErrorClass}`
    });
    attributeSelector.configure().then(() => {
      attributeSelector.setParent(this);
      attributeSelector.render();
    });

    attributeSelector.on('post_render', () => {
      if (isAttributeOptionsButtonVisible) {
        attributeSelector.$el.find('.icons-container').append(
          $('<div>')
            .addClass('AknIconButton AknIconButton--small AknIconButton--edit AknGrid-onHoverElement option-mapping')
            .attr('data-franklin-attribute-code', franklinAttributeCode)
            .attr('title', __('pim_common.edit'))
        );
      }

      $dom.prepend(attributeSelector.$el);
    });

    return attributeSelector;
  }

  private appendCreateAttributeButton(
    franklinAttributeCode: string,
    familyCode: string,
    franklinAttributeLabel: string,
    franklinAttributeType: string
  ) {
    const createAttributeButton = new CreateAttributeButton(familyCode, franklinAttributeLabel, franklinAttributeType);

    const $host = this.$el.find(`.create-attribute-button[data-franklin-attribute-code="${franklinAttributeCode}"]`);
    $host.append(createAttributeButton.render().el);

    return createAttributeButton;
  }

  private appendAddAttributeToFamilyButton(
    attributeCode: string,
    familyCode: string
  ) {
    const addAttributeToFamilyButton = new AddAttributeToFamilyButton(familyCode, attributeCode);

    const $host = this.$el.find(`.add-attribute-to-family-button[data-franklin-attribute-code="${attributeCode}"]`);
    $host.append(addAttributeToFamilyButton.render().el);

    return addAttributeToFamilyButton;
  }

  private isTypeMappingValid(franklinAttributeType: string, pimAttributeType: string) {
    return PERFECT_MAPPINGS[franklinAttributeType].includes(pimAttributeType);
  }

  private appendAttributeTypeMismatchWarning(franklinAttributeCode: string) {
    const attributeTypeMismatchWarning = new AttributeTypeMismatchWarning();

    const $host = this.$el.find(
      `.attribute-type-mismatch-warning[data-franklin-attribute-code="${franklinAttributeCode}"]`
    );
    $host.append(attributeTypeMismatchWarning.render().el);

    return attributeTypeMismatchWarning;
  }

  /**
   * Get the family code from current URL
   */
  private getFamilyCode() {
    return Router.match(window.location.hash).params.familyCode;
  }

  /**
   * Open the modal for the attribute options mapping
   */
  private openAttributeOptionsMappingModal(event: {currentTarget: any}) {
    const $line = $(event.currentTarget).closest('.line');
    const franklinAttributeLabel = $line.data('franklin-attribute') as string;
    const franklinAttributeCode = $line.find('.attribute-selector').data('franklin-attribute-code');
    const catalogAttributeCode = $line
      .find('input[name="' + Property.propertyPath(['mapping', franklinAttributeCode, 'attribute']) + '"]')
      .val() as string;
    const familyCode = this.getFamilyCode();

    $.when(
      FormBuilder.build('akeneo-franklin-insights-settings-attribute-options-mapping-edit'),
      FetcherRegistry.getFetcher('family').fetch(familyCode)
    ).then((form: BaseView, normalizedFamily: any) => {
      const familyLabel = i18n.getLabel(
        normalizedFamily.labels,
        UserContext.get('catalogLocale'),
        normalizedFamily.code
      );

      const formContent = form.getExtension('content') as AttributeOptionsMapping;
      formContent.initializeMapping(familyCode, catalogAttributeCode, franklinAttributeCode).then(() => {
        this.attributeOptionsMappingModal = new (Backbone as any).BootstrapModal({
          modalOptions: {
            backdrop: 'static',
            keyboard: false
          },
          okCloses: false,
          title: __('akeneo_franklin_insights.entity.attribute_options_mapping.module.edit.title', {
            familyLabel,
            franklinAttributeLabel
          }),
          content: form,
          template: this.modalTemplate,
          innerClassName: 'AknFullPage--full AknFullPage--fixedWidth',
          okText: ''
        });
        this.attributeOptionsMappingModal.on('cancel', this.closeOptionsMappingAfterCancel.bind(this));
        this.attributeOptionsMappingModal.open();
        this.attributeOptionsMappingForm = form;

        this.listenTo(form, 'pim_enrich:form:entity:post_save', this.closeAttributeOptionsMappingModal.bind(this));
      });
    });
  }

  /**
   * Prevents closing if there are unsaved changes
   */
  private closeOptionsMappingAfterCancel(): void {
    const canLeaveEvent = {canLeave: true};

    if (null !== this.attributeOptionsMappingForm) {
      this.attributeOptionsMappingForm.trigger('pim_enrich:form:can-leave', canLeaveEvent);
    }
    this.attributeOptionsMappingModal._preventClose = false;
    if (!canLeaveEvent.canLeave) {
      this.attributeOptionsMappingModal._preventClose = true;
      return;
    }

    this.closeAttributeOptionsMappingModal();
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

  /**
   * Return true if the franklin attribute can be deactivated.
   */
  private isFranklinAttributeDeactivable(mapping: IAttributeMapping): boolean {
    return mapping.status !== AttributeMappingStatus.ATTRIBUTE_INACTIVE;
  }

  /**
   * Display a confirmation modal for the deactivation of a franklin attribute.
   */
  private renderDeactivateFranklinAttributeConfirmationModal(): Promise<void> {
    const modal = new (Backbone as any).BootstrapModal({
      title: __('akeneo_franklin_insights.entity.attributes_mapping.modal.deactivate_franklin_attribute.title'),
      subtitle: __('akeneo_franklin_insights.entity.attributes_mapping.modal.deactivate_franklin_attribute.subtitle'),
      content: '',
      illustrationClass: 'delete',
      okText: __('akeneo_franklin_insights.entity.attributes_mapping.modal.deactivate_franklin_attribute.ok'),
      buttonClass: 'AknButton--important'
    });

    modal.open();

    return new Promise((resolve, reject) => {
      this.listenTo(modal, 'ok', resolve);
      this.listenTo(modal, 'cancel', reject);
    });
  }

  /**
   * Deactivate a franklin attribute.
   */
  private async deactivateFranklinAttribute(franklinAttributeCode: string) {
    try {
      await this.renderDeactivateFranklinAttributeConfirmationModal();
    } catch (e) {
      return;
    }

    const familyMapping = this.getFormData() as IAttributesMappingForFamily;

    familyMapping.mapping[franklinAttributeCode].status = AttributeMappingStatus.ATTRIBUTE_INACTIVE;

    const request = {
      code: familyMapping.code,
      mapping: {
        [franklinAttributeCode]: familyMapping.mapping[franklinAttributeCode]
      }
    };

    await ajax({
      url: Routing.generate('akeneo_franklin_insights_attributes_mapping_update', {identifier: familyMapping.code}),
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify(request)
    });

    this.getRoot().trigger('franklin_attribute_deactivated', franklinAttributeCode);
    this.setData(familyMapping);

    this.render();

    Messenger.notify(
      'success',
      __('akeneo_franklin_insights.entity.attributes_mapping.flash.do_not_map_attribute_success')
    );
  }
}

export = AttributeMapping;
