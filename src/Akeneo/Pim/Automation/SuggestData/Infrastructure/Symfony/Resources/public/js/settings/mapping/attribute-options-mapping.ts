/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as $ from 'jquery';
import BaseForm = require('pimenrich/js/view/base');
import * as _ from 'underscore';
import {Filterable} from '../../common/filterable';
import {
  AttributeOptionStatus,
  NormalizedAttributeOptionsMapping,
} from '../../model/normalized-attribute-options-mapping';
const __ = require('oro/translator');
const SimpleSelectAsync = require('pim/form/common/fields/simple-select-async');
const FetcherRegistry = require('pim/fetcher-registry');
const Routing = require('routing');
const template = require('pimee/template/settings/mapping/attribute-options-mapping');

interface Config {
  labels: {
    pending: string;
    mapped: string;
    unmapped: string;
    franklin_attribute_option: string;
    catalog_attribute_option: string;
    attribute_option_status: string;
  };
}

/**
 * Displays the full modal for the attribute options mapping.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class AttributeOptionsMapping extends BaseForm {
  public readonly template: any = _.template(template);
  public readonly config: Config = {
    labels: {
      pending: '',
      mapped: '',
      unmapped: '',
      franklin_attribute_option: '',
      catalog_attribute_option: '',
      attribute_option_status: '',
    },
  };
  private familyLabel: string;
  private familyCode: string;
  private catalogAttributeCode: string;
  private franklinAttributeLabel: string;

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
    if (Object.keys(this.getFormData()).length === 0) {
      this.fetchMapping().then((attributeOptionsMapping: NormalizedAttributeOptionsMapping) => {
        this.setData(attributeOptionsMapping);
        this.innerRender();
      });
    } else {
      this.innerRender();
    }
    return this;
  }

  /**
   * Sets the Family label (for the header display)
   *
   * @param {string} familyLabel
   * @return AttributeOptionsMapping
   */
  public setFamilyLabel(familyLabel: string): AttributeOptionsMapping {
    this.familyLabel = familyLabel;

    return this;
  }

  /**
   * Sets the Franklin attribute label (for the header display)
   *
   * @param {string} franklinAttributeLabel
   * @return AttributeOptionsMapping
   */
  public setFranklinAttributeLabel(franklinAttributeLabel: string): AttributeOptionsMapping {
    this.franklinAttributeLabel = franklinAttributeLabel;

    return this;
  }

  /**
   * Sets the Catalog family code (for the current attribute options fetching)
   *
   * @param {string} familyCode
   * @return AttributeOptionsMapping
   */
  public setFamilyCode(familyCode: string): AttributeOptionsMapping {
    this.familyCode = familyCode;

    return this;
  }

  /**
   * Sets the Catalog attribute code (for the current attribute options fetching)
   *
   * @param {string} catalogAttributeCode
   * @return AttributeOptionsMapping
   */
  public setCatalogAttributeCode(catalogAttributeCode: string): AttributeOptionsMapping {
    this.catalogAttributeCode = catalogAttributeCode;

    return this;
  }

  /**
   * @returns {{ [ key: number ]: string }}
   */
  private getMappingStatuses() {
    const statuses: { [key: number]: string } = {};
    statuses[AttributeOptionStatus.Pending] = __(this.config.labels.pending);
    statuses[AttributeOptionStatus.Mapped] = __(this.config.labels.mapped);
    statuses[AttributeOptionStatus.Unmapped] = __(this.config.labels.unmapped);

    return statuses;
  }

  /**
   * Fetch the mapping and return a
   *
   * @return { JQueryPromise<NormalizedAttributeOptionsMapping> }
   */
  private fetchMapping(): JQueryPromise<NormalizedAttributeOptionsMapping> {
    return $.when(
      FetcherRegistry
        .getFetcher('attribute-options-mapping')
        .fetch(this.familyCode, {attributeCode: this.catalogAttributeCode, cached: false})
        .then((attributeOptionMapping: NormalizedAttributeOptionsMapping) => {
          return attributeOptionMapping;
        }),
    );
  }

  /**
   * Renders the full modal
   */
  private innerRender(): void {
    const mapping = this.getFormData().mapping as NormalizedAttributeOptionsMapping;
    this.$el.html(this.template({
      title: __('akeneo_suggest_data.entity.attribute_options_mapping.module.edit.title', {
        familyLabel: this.familyLabel,
        franklinAttributeLabel: this.franklinAttributeLabel,
      }),
      mapping,
      franklin_attribute_option: __(this.config.labels.franklin_attribute_option),
      catalog_attribute_option: __(this.config.labels.catalog_attribute_option),
      attribute_option_status: __(this.config.labels.attribute_option_status),
      statuses: this.getMappingStatuses(),
    }));

    Object.keys(mapping).forEach((franklinAttributeOptionCode: string) => {
      this.appendAttributeOptionSelector(franklinAttributeOptionCode);
    });

    Filterable.afterRender(this, __(
      'akeneo_suggest_data.entity.attribute_options_mapping.fields.franklin_attribute_option',
    ));

    this.renderExtensions();

    // Not optimal solution, but we didn't find a better one; this code will move the save button in the modal element.
    $('.modal .modal-footer *[data-drop-zone="buttons"]').remove();
    $('.modal .modal-footer').append(this.getRoot().$el.find('*[data-drop-zone="buttons"]'));
  }

  /**
   * @param {string} franklinAttributeOptionCode
   */
  private appendAttributeOptionSelector(franklinAttributeOptionCode: string) {
    const $dom = this.$el.find(
      '.attribute-selector[data-franklin-attribute-code="' + franklinAttributeOptionCode + '"]',
    );
    const attributeSelector = new SimpleSelectAsync({
      config: {
        fieldName: 'mapping.' + franklinAttributeOptionCode + '.catalog_attribute_option_code',
        label: '',
      },
      className: 'AknFieldContainer AknFieldContainer--withoutMargin AknFieldContainer--inline',
    });
    attributeSelector.setChoiceUrl(
      Routing.generate('pim_enrich_attributeoption_get', {identifier: this.catalogAttributeCode}),
    );
    attributeSelector.configure().then(() => {
      attributeSelector.setParent(this);
      $dom.html(attributeSelector.render().$el);
    });
  }
}

export = AttributeOptionsMapping;
