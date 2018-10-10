/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import BaseForm = require('pimenrich/js/view/base');
import * as _ from "underscore";
import Filterable = require('akeneosuggestdata/js/common/filterable');
import * as $ from "jquery";
const __ = require('oro/translator');
const SimpleSelectAsync = require('pim/form/common/fields/simple-select-async');
const FetcherRegistry = require('pim/fetcher-registry');
const Routing = require('routing');
const template = require('pimee/template/settings/mapping/attribute-options-mapping');

interface NormalizedAttributeOptionsMapping {
  family: string;
  pim_ai_attribute: string;
  mapping: {
    [pim_ai_attribute_option_code: string] : {
      pim_ai_attribute_option_code: {
        label: string;
      },
      attribute_option: string;
      status: number;
    }
  }
}

interface Config {
  labels: {
    pending: string;
    mapped: string;
    unmapped: string;
    pim_ai_attribute_option: string;
    catalog_attribute_option: string;
    suggest_data: string;
  }
}

/**
 * Displays the full modal for the attribute options mapping.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class AttributeOptionsMapping extends BaseForm {
  private static readonly ATTRIBUTE_OPTION_PENDING: number = 0;
  private static readonly ATTRIBUTE_OPTION_MAPPED: number = 1;
  private static readonly ATTRIBUTE_OPTION_UNMAPPED: number = 2;
  readonly template: any = _.template(template);
  private familyLabel: string;
  private familyCode: string;
  private pimAttributeCode: string;
  private pimAiAttributeLabel: string;
  readonly config: Config = {
    labels: {
      pending: '',
      mapped: '',
      unmapped: '',
      pim_ai_attribute_option: '',
      catalog_attribute_option: '',
      suggest_data: '', // TODO Rename to attribute_option_code_mapping
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
   * Sets the PIM.ai attribute label (for the header display)
   *
   * @param {string} pimAiAttributeLabel
   * @return AttributeOptionsMapping
   */
  public setPimAiAttributeLabel(pimAiAttributeLabel: string): AttributeOptionsMapping {
    this.pimAiAttributeLabel = pimAiAttributeLabel;

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
   * Sets the PIM.ai attribute code (for the current attribute options fetching)
   *
   * @param {string} pimAttributeCode
   * @return AttributeOptionsMapping
   */
  public setPimAttributeCode(pimAttributeCode: string): AttributeOptionsMapping {
    this.pimAttributeCode = pimAttributeCode;

    return this;
  }

  /**
   * @returns {{ [ key: number ]: string }}
   */
  private getMappingStatuses() {
    const statuses: { [key: number]: string } = {};
    statuses[AttributeOptionsMapping.ATTRIBUTE_OPTION_PENDING] = __(this.config.labels.pending);
    statuses[AttributeOptionsMapping.ATTRIBUTE_OPTION_MAPPED] = __(this.config.labels.mapped);
    statuses[AttributeOptionsMapping.ATTRIBUTE_OPTION_UNMAPPED] = __(this.config.labels.unmapped);

    return statuses;
  }

  /**
   * Fetch the mapping and return a
   *
   * @return {JQueryPromise<NormalizedAttributeOptionsMapping>}
   */
  private fetchMapping(): JQueryPromise<NormalizedAttributeOptionsMapping> {
    return $.when(
      FetcherRegistry
        .getFetcher('attribute-options-mapping')
        .fetch(this.familyCode, {attributeCode: this.pimAttributeCode})
        .then((attributeOptionMapping: NormalizedAttributeOptionsMapping) => {
          return attributeOptionMapping;
        })
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
        pimAiAttributeLabel: this.pimAiAttributeLabel,
      }),
      mapping,
      pim_ai_attribute_option: __(this.config.labels.pim_ai_attribute_option),
      catalog_attribute_option: __(this.config.labels.catalog_attribute_option),
      suggest_data: __(this.config.labels.suggest_data),
      statuses: this.getMappingStatuses(),
    }));

    Object.keys(mapping).forEach((pimAiAttributeOptionCode: string) => {
      this.appendAttributeOptionSelector(pimAiAttributeOptionCode);
    });

    Filterable.afterRender(this, __(
      'akeneo_suggest_data.entity.attribute_options_mapping.fields.pim_ai_attribute_option'
    ));

    this.renderExtensions();

    // Not optimal solution, but we didn't find a better one; this code will move the save button in the modal element.
    $('.modal .modal-footer *[data-drop-zone="buttons"]').remove();
    $('.modal .modal-footer').append(this.getRoot().$el.find('*[data-drop-zone="buttons"]'));
  }

  /**
   * @param {string} pimAiAttributeOptionCode
   */
  private appendAttributeOptionSelector(pimAiAttributeOptionCode: string) {
    const $dom = this.$el.find(
      '.attribute-selector[data-pim-ai-attribute-code="' + pimAiAttributeOptionCode + '"]'
    );
    const attributeSelector = new SimpleSelectAsync({
      config: {
        fieldName: 'mapping.' + pimAiAttributeOptionCode + '.attribute_option',
        label: '',
      },
      className: 'AknFieldContainer AknFieldContainer--withoutMargin AknFieldContainer--inline'
    });
    attributeSelector.setChoiceUrl(
      Routing.generate('pim_enrich_attributeoption_get', {identifier: this.pimAttributeCode})
    );
    attributeSelector.configure().then(() => {
      attributeSelector.setParent(this);
      $dom.html(attributeSelector.render().$el);
    });
  }
}

export = AttributeOptionsMapping
