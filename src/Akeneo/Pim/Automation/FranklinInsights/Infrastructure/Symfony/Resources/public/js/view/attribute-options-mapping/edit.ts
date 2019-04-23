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
const Routing = require('routing');
const template = require('akeneo/franklin-insights/template/settings/attribute-options-mapping/edit');

interface Config {
  labels: {
    pending: string;
    mapped: string;
    unmapped: string;
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
      mapped: '',
      unmapped: '',
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
   * @param familyCode
   * @param catalogAttributeCode
   * @param franklinAttributeCode
   *
   * @returns {any|JQuery.Promise<any, any, never>}
   */
  public initializeMapping(
    familyCode: string,
    catalogAttributeCode: string,
    franklinAttributeCode: string
  ): JQueryPromise<any> {
    this.familyCode = familyCode;
    this.catalogAttributeCode = catalogAttributeCode;
    this.franklinAttributeCode = franklinAttributeCode;

    return $.when(
      this.fetchMapping().then((attributeOptionsMapping: NormalizedAttributeOptionsMapping) => {
        attributeOptionsMapping.catalogAttributeCode = this.catalogAttributeCode;
        this.setData(attributeOptionsMapping);
      })
    );
  }

  /**
   * {@inheritdoc}
   */
  public render(): BaseForm {
    const mapping = this.getFormData().mapping as NormalizedAttributeOptionsMapping;
    this.$el.html(this.template({
      mapping,
      franklinAttributeOption: __(this.config.labels.franklinAttributeOption),
      catalogAttributeOption: __(this.config.labels.catalogAttributeOption),
      attributeOptionStatus: __(this.config.labels.attributeOptionStatus),
      statuses: this.getMappingStatuses(),
    }));

    $.when(
      Object.keys(mapping).forEach((franklinAttributeOptionCode: string) => {
        this.appendAttributeOptionSelector(franklinAttributeOptionCode);
      })
    ).then(() => {
      Filterable.afterRender(this, __(this.config.labels.franklinAttributeOption));

      this.renderExtensions();
    });

    return this;
  }

  /**
   * @returns {{ [ status: number ]: string }}
   */
  private getMappingStatuses() {
    const statuses: { [status: number]: string } = {};
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
        .fetch(this.familyCode, {franklinAttributeCode: this.franklinAttributeCode, cached: false})
        .then((attributeOptionMapping: NormalizedAttributeOptionsMapping) => {
          return attributeOptionMapping;
        }),
    );
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
        fieldName: 'mapping.' + franklinAttributeOptionCode + '.catalogAttributeOptionCode',
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
