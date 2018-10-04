//import * as _ from 'underscore';

//const __ = require('oro/translator');
const BaseSimpleSelect = require('pim/form/common/fields/simple-select-async');
//const i18n = require('pim/i18n');
//const UserContext = require('pim/user-context');
//const FetcherRegistry = require('pim/fetcher-registry');
//const LineTemplate = require('pimee/template/settings/mapping/attribute-line');

/**
 * Attributes simple select
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
/*interface NormalizedAttributeInterface {
  code: string;
  labels: { [key: string]: string };
  group: string;
}

interface NormalizedAttributeGroupInterface {
  labels: { [key: string]: string };
}*/

class SimpleSelectAttributeOption extends BaseSimpleSelect {
  //readonly lineView = _.template(LineTemplate);
  //private attributeGroups: { [key: string]: NormalizedAttributeGroupInterface } = {};

  constructor(options: { config: Object, className: string }) {
    super({
      ...{ className: 'AknFieldContainer AknFieldContainer--withoutMargin' }, ...options
    });
  }

  /**
   * {@inheritdoc}
   */
  /*
  public configure(): JQueryPromise<any> {
    return $.when(
      BaseSimpleSelect.prototype.configure.apply(this, arguments),
      FetcherRegistry
        .getFetcher('attribute-group')
        .fetchAll()
        .then((attributeGroups: { [key: string]: NormalizedAttributeGroupInterface }) => {
          this.attributeGroups = attributeGroups;
        })
    );
  }*/

  /**
   * {@inheritdoc}
   */
  /*
  public getSelect2Options(): any {
    const parent = BaseSimpleSelect.prototype.getSelect2Options.apply(this, arguments);
    parent.allowClear = true;
    parent.formatResult = this.onGetResult.bind(this);
    parent.dropdownCssClass = 'select2--annotedLabels ' + parent.dropdownCssClass;

    return parent;
  }
  */

  /**
   * {@inheritdoc}
   */
  /*
  protected convertBackendItem(item: NormalizedAttributeInterface): Object {
    return {
      id: item.code,
      text: i18n.getLabel(item.labels, UserContext.get('catalogLocale'), item.code),
      group: {
        text: (
          item.group ?
            i18n.getLabel(
              this.attributeGroups[item.group].labels,
              UserContext.get('catalogLocale'),
              this.attributeGroups[item.group]
            ) : ''
        )
      }
    };
  }
  */

  /**
   * {@inheritdoc}
   *
   * Removes the useless catalogLocale field, and adds localizable, is_locale_specific and scopable filters.
   */
  /*
  protected select2Data(term: string, page: number): Object {
    return {
      localizable: false,
      is_locale_specific: false,
      scopable: false,
      search: term,
      types: this.config.types.join(','),
      options: {
        limit: this.resultsPerPage,
        page: page
      }
    };
  }
  */

  /**
   * {@inheritdoc}
   *
   * Has been overrode because translations should be handle front side.
   * Translates messages.
   */
  /*
  protected getFieldErrors(errors: { [index: string] : { message: string, messageParams: any } }) {
    Object.keys(errors).map(index => {
      errors[index].message = __(errors[index].message, errors[index].messageParams);
    });
    return BaseSimpleSelect.prototype.getFieldErrors.apply(this, arguments);
  }
  */

  /**
   * Formats and updates list of items
   *
   * @param {Object} item
   *
   * @return {Object}
   */
  /*
  private onGetResult(item: { text: string, group: { text: string } }): Object {
    return this.lineView({item});
  }
  */
}

export = SimpleSelectAttributeOption
