import * as _ from 'underscore';

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

/** Defined in Akeneo/Pim/Automation/SuggestData/Infrastructure/Controller/AttributeMappingController.php */
const MAPPING_EMPTY: number = 0;
const MAPPING_FULL: number = 1;
const MAPPING_PENDING_ATTRIBUTES: number = 2;

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
      case MAPPING_FULL:
        result.className = 'select2-result-label-attribute select2-result-label-attribute--full';
        break;
      case MAPPING_PENDING_ATTRIBUTES:
        result.className = 'select2-result-label-attribute select2-result-label-attribute--pending';
        break;
      case MAPPING_EMPTY:
      default:
        result.className = '';
    }
    return result;
  }
}

export = FamilySelector;
