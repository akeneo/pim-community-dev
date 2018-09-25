import * as $ from 'jquery';
import * as i18n from 'pimenrich/js/i18n';
import * as _ from "underscore";

const BaseMultiSelectAsync = require('pim/form/common/fields/multi-select-async');
const UserContext = require('pim/user-context');
const FetcherRegistry = require('pim/fetcher-registry');
const LineTemplate = require('pim/template/attribute/attribute-line');

/**
 * Product grid filters select. It's a multi-select for attributes.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */

interface NormalizedAttributeInterface {
  code: string;
  labels: { [key: string]: string };
  group: string;
}

interface NormalizedAttributeGroupInterface {
  labels: { [key: string]: string };
}

class ProductGridFilters extends BaseMultiSelectAsync {
  private readonly lineView = _.template(LineTemplate);
  private attributeGroups: { [key: string]: NormalizedAttributeGroupInterface } = {};

  /**
   * {@inheritdoc}
   */
  public configure(): JQueryPromise<any> {
    return $.when(
      BaseMultiSelectAsync.prototype.configure.apply(this, arguments),
      FetcherRegistry
        .getFetcher('attribute-group')
        .fetchAll()
        .then((attributeGroups: { [key: string]: NormalizedAttributeGroupInterface }) => {
          this.attributeGroups = attributeGroups;
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

  /**
   * {@inheritdoc}
   *
   * Removes the useless catalogLocale field, and add grid filter
   */
  protected select2Data(term: string, page: number) {
    return {
      // TODO Adds the product grid filters
      search: term,
      options: {
        limit: this.resultsPerPage,
        page: page
      },
      useable_as_grid_filter: true
    };
  }

  protected convertBackendItem(item: NormalizedAttributeInterface): Object {
    return {
      id: item.code,
      text: i18n.getLabel(item.labels, UserContext.get('catalog_default_locale'), item.code),
      group: {
        text: (
          item.group ?
            i18n.getLabel(
              this.attributeGroups[item.group].labels,
              UserContext.get('catalog_default_locale'),
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
    const strValues:string = (<any> $(element)).val();
    const values = strValues.split(',');
    if (values.length > 0) {
      $.ajax({
        url: this.choiceUrl,
        data: { identifiers: strValues },
        type: this.choiceVerb
      }).then(response => {
        let selecteds: NormalizedAttributeInterface[] = <NormalizedAttributeInterface[]> Object.values(response)
          .filter((item: NormalizedAttributeInterface) => {
            return values.indexOf(item.code) > -1;
          });

        callback(selecteds.map((selected: NormalizedAttributeInterface) => {
          return this.convertBackendItem(selected);
        }));
      });
    }
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
