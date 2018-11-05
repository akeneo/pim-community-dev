/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as _ from 'underscore';
import AttributeMapping = require('./table');

const BaseSelect = require('pim/form/common/fields/simple-select-async');
const FetcherRegistry = require('pim/fetcher-registry');
const Router = require('pim/router');
const lineTemplate = require('pimee/template/attributes-mapping/family-line');

interface Config {
  fieldName: string;
  label: string;
  choiceRoute: string;
}

/**
 * This module allow user to select a catalog family for suggest data updating.
 * When he selects a new family, it updates the main root model with it.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class FamilySelector extends BaseSelect {
  private readonly lineView = _.template(lineTemplate);

  constructor(config: { config: Config }) {
    super(config);
    this.events = {
      'change input': (event: { target: any }) => {
        FetcherRegistry.getFetcher('suggest_data_attribute_mapping_by_family')
          .fetch(this.getFieldValue(event.target), {cached: false})
          .then((family: { code: string }) => {
            const hasRedirected = Router.redirectToRoute('akeneo_suggest_data_attributes_mapping_edit', {
              familyCode: family.code,
            });
            if (false === hasRedirected) {
              this.render();
            } else {
              return hasRedirected;
            }
          });
      },
    };
  }

  /**
   * {@inheritdoc}
   */
  public getSelect2Options() {
    const parent = BaseSelect.prototype.getSelect2Options.apply(this, arguments);
    parent.formatResult = this.onGetResult.bind(this);
    parent.dropdownCssClass = 'select2--withIcon ' + parent.dropdownCssClass;

    return parent;
  }

  /**
   * Formats and updates list of items
   *
   * @param {object} item
   *
   * @return {object}
   */
  public onGetResult(item: { text: string }) {
    return this.lineView({item});
  }

  /**
   * {@inheritdoc}
   */
  public convertBackendItem(item: { status: number }) {
    const result = BaseSelect.prototype.convertBackendItem.apply(this, arguments);
    switch (item.status) {
      case AttributeMapping.FAMILY_MAPPING_FULL:
        result.className = 'select2-result-label-attribute select2-result-label-attribute--full';
        break;
      case AttributeMapping.FAMILY_MAPPING_PENDING:
        result.className = 'select2-result-label-attribute select2-result-label-attribute--pending';
        break;
      case AttributeMapping.FAMILY_MAPPING_EMPTY:
      default:
        result.className = '';
    }
    return result;
  }
}

export = FamilySelector;
