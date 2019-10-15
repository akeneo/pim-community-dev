import $ from 'jquery';
import * as i18n from 'pimui/js/i18n';
import * as _ from 'underscore';
import NormalizedAttribute from 'pim/model/attribute';
import NormalizedAttributeGroup from 'pim/model/attribute-group';

const __ = require('oro/translator');
const BaseMultiSelectAsync = require('pim/form/common/fields/multi-select-async');
const UserContext = require('pim/user-context');
const FetcherRegistry = require('pim/fetcher-registry');
const LineTemplate = require('pim/template/attribute/attribute-line');

/**
 * Product grid filters select. It's a multi-select for attributes.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class ProductGridFilters extends BaseMultiSelectAsync {
  private readonly lineView = _.template(LineTemplate);
  private attributeGroups: { [key: string]: NormalizedAttributeGroup } = {};

  /**
   * {@inheritdoc}
   */
  public configure(): JQueryPromise<any> {
    this.attributeGroups = {
      system: ProductGridFilters.getSystemAttributeGroup()
    };

    return $.when(
      BaseMultiSelectAsync.prototype.configure.apply(this, arguments),
      FetcherRegistry
        .getFetcher('attribute-group')
        .fetchAll()
        .then((attributeGroups: { [key: string]: NormalizedAttributeGroup }) => {
          this.attributeGroups = {...this.attributeGroups, ...attributeGroups};
        })
    );
  }

  /**
   * {@inheritdoc}
   */
  public getSelect2Options(): any {
    const parent = BaseMultiSelectAsync.prototype.getSelect2Options.apply(this, arguments);
    parent.formatResult = this.onGetResult.bind(this);
    parent.dropdownCssClass = 'select2--annotedLabels ' + parent.dropdownCssClass;

    return parent;
  }

  protected convertBackendItem(item: NormalizedAttribute): Object {
    return {
      id: item.code,
      text: i18n.getLabel(item.labels, UserContext.get('catalogLocale'), item.code),
      group: {
        text: (
          item.group ?
            i18n.getLabel(
              this.attributeGroups[item.group].labels,
              UserContext.get('catalogLocale'),
              item.group
            ) : ''
        )
      }
    };
  }

  /**
   * {@inheritdoc}
   */
  protected select2InitSelection(element: any, callback: any): void {
    const strValues = ($(element)).val() as string;
    const values = strValues.split(',');
    if (values.length > 0) {
      $.ajax({
        url: this.choiceUrl,
        data: { identifiers: strValues },
        type: this.choiceVerb
      }).then(response => {
        let selecteds: NormalizedAttribute[] = <NormalizedAttribute[]> Object.values(response)
          .filter((item: NormalizedAttribute) => {
            return values.indexOf(item.code) > -1;
          });

        callback(selecteds.map((selected: NormalizedAttribute) => {
          return this.convertBackendItem(selected);
        }));
      });
    }
  }

  /**
   * Returns a fake attribute group for system filters
   *
   * @returns {NormalizedAttributeGroup}
   */
  private static getSystemAttributeGroup(): NormalizedAttributeGroup {
    const result: NormalizedAttributeGroup = {labels: {}};
    result['labels'][UserContext.get('catalogLocale')] = __('pim_datagrid.filters.system');

    return result;
  }

  /**
   * Formats and updates list of items
   *
   * @param {Object} item
   *
   * @return {Object}
   */
  private onGetResult(item: { text: string, group: { text: string } }): Object {
    return this.lineView({item});
  }
}

export = ProductGridFilters
