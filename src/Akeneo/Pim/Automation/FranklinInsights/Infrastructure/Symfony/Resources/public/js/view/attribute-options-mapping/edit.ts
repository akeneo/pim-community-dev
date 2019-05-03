/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as $ from 'jquery';
import BaseForm = require('pimui/js/view/base');
import * as _ from 'underscore';
import {Filterable} from '../../common/filterable';
import {
  AttributeOptionStatus,
  NormalizedAttributeOptionsMapping,
} from '../../model/normalized-attribute-options-mapping';

const __ = require('oro/translator');
const SimpleSelectAsync = require('pim/form/common/fields/simple-select-async');
const FetcherRegistry = require('pim/fetcher-registry');
const Property = require('pim/common/property');
const Routing = require('routing');
const template = require('akeneo/franklin-insights/template/settings/attribute-options-mapping/edit');

interface Config {
  labels: {
    pending: string;
    active: string;
    inactive: string;
    franklinAttributeOption: string;
    catalogAttributeOption: string;
    attributeOptionStatus: string;
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
      active: '',
      inactive: '',
      franklinAttributeOption: '',
      catalogAttributeOption: '',
      attributeOptionStatus: '',
    },
  };
  private familyCode: string;
  private catalogAttributeCode: string;
  private franklinAttributeCode: string;

  /**
   * {@inheritdoc}
   */
  constructor(options: { config: Config }) {
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
        attributeOptionsMapping.catalogAttributeCode = this.catalogAttributeCode;
        this.setData(attributeOptionsMapping);
        this.innerRender();
      });
    } else {
      this.innerRender();
    }
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
   * Sets the Franklin attribute code
   *
   * @param {string} franklinAttributeCode
   * @return AttributeOptionsMapping
   */
  public setFranklinAttributeCode(franklinAttributeCode: string): AttributeOptionsMapping {
    this.franklinAttributeCode = franklinAttributeCode;

    return this;
  }

  /**
   * @returns {{ [ status: number ]: string }}
   */
  private getMappingStatuses() {
    const statuses: { [status: number]: string } = {};
    statuses[AttributeOptionStatus.Pending] = __(this.config.labels.pending);
    statuses[AttributeOptionStatus.Active] = __(this.config.labels.active);
    statuses[AttributeOptionStatus.Inactive] = __(this.config.labels.inactive);

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
        .fetch(this.familyCode, {franklinAttributeCode: this.franklinAttributeCode, cached: false})
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
      mapping,
      franklinAttributeOption: __(this.config.labels.franklinAttributeOption),
      catalogAttributeOption: __(this.config.labels.catalogAttributeOption),
      attributeOptionStatus: __(this.config.labels.attributeOptionStatus),
      statuses: this.getMappingStatuses(),
    }));

    Object.keys(mapping).forEach((franklinAttributeOptionCode: string) => {
      this.appendAttributeOptionSelector(franklinAttributeOptionCode);
    });

    Filterable.afterRender(this, __(this.config.labels.franklinAttributeOption));

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
        fieldName: Property.propertyPath(['mapping', franklinAttributeOptionCode, 'catalogAttributeOptionCode']),
        label: '',
      },
      className: 'AknFieldContainer AknFieldContainer--withoutMargin AknFieldContainer--inline',
    });
    attributeSelector.allowClear = true;
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
