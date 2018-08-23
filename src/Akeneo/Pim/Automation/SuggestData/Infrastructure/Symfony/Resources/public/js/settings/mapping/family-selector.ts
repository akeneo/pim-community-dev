import * as _ from 'underscore';
const BaseSelect = require('pim/form/common/fields/simple-select-async');
const FetcherRegistry = require('pim/fetcher-registry');
const Router = require('pim/router');
const lineTemplate = require('pimee/template/settings/mapping/family-line');

/**
 * This module allow user to select a catalog family for suggest data updating.
 * When he selects a new family, it updates the main root model with it.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class FamilySelector extends BaseSelect {
  readonly lineView = _.template(lineTemplate);

  constructor(config: { config: Object }) {
    super(config);
    this.events = {
      'change input': (event: { target: any }) => {
        FetcherRegistry.getFetcher('suggest_data_attribute_mapping_by_family')
          .fetch(this.getFieldValue(event.target), {cached: false})
          .then((family: { code: string }) => {
            const hasRedirected = Router.redirectToRoute('akeneo_suggest_data_attributes_mapping_edit', {
              familyCode: family.code
            });
            if (false === hasRedirected) {
              this.render();
            } else {
              return hasRedirected;
            }
          });
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  getSelect2Options() {
    const parent = BaseSelect.prototype.getSelect2Options.apply(this, arguments);
    parent.formatResult = this.onGetResult.bind(this);
    parent.dropdownCssClass = 'select2--withIcon ' + parent.dropdownCssClass;
    return parent;
  }

  /**
   * Formats and updates list of items
   *
   * @param {Object} item
   *
   * @return {Object}
   */
  onGetResult(item: { text: string }) {
    return this.lineView({item});
  }

  /**
   * {@inheritdoc}
   */
  convertBackendItem(item: { enabled: boolean }) {
    const result = BaseSelect.prototype.convertBackendItem.apply(this, arguments);
    result.enabled = item.enabled;
    return result;
  }
}

export = FamilySelector
