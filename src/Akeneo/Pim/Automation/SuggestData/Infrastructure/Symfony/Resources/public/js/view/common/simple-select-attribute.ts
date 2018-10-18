import * as _ from 'underscore';

const __ = require('oro/translator');
const BaseSimpleSelect = require('pim/form/common/fields/simple-select-async');
const i18n = require('pim/i18n');
const UserContext = require('pim/user-context');
const FetcherRegistry = require('pim/fetcher-registry');
const LineTemplate = require('pimee/template/common/attribute-line');

/**
 * Attributes simple select
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
interface NormalizedAttribute {
  code: string;
  labels: { [pim_attribute_code: string]: string };
  group: string;
}

interface NormalizedAttributeGroup {
  labels: { [pim_attribute_group_code: string]: string };
}

class SimpleSelectAttribute extends BaseSimpleSelect {
  private readonly lineView = _.template(LineTemplate);
  private attributeGroups: { [pim_attribute_group_code: string]: NormalizedAttributeGroup } = {};

  constructor(options: { config: object, className: string }) {
    super({
      ...{ className: 'AknFieldContainer AknFieldContainer--withoutMargin' }, ...options,
    });
  }

  /**
   * {@inheritdoc}
   */
  public configure(): JQueryPromise<any> {
    return $.when(
      BaseSimpleSelect.prototype.configure.apply(this, arguments),
      FetcherRegistry
        .getFetcher('attribute-group')
        .fetchAll()
        .then((attributeGroups: { [pim_attribute_group_code: string]: NormalizedAttributeGroup }) => {
          this.attributeGroups = attributeGroups;
        }),
    );
  }

  /**
   * {@inheritdoc}
   */
  public getSelect2Options(): any {
    const parent = BaseSimpleSelect.prototype.getSelect2Options.apply(this, arguments);
    parent.allowClear = true;
    parent.formatResult = this.onGetResult.bind(this);
    parent.dropdownCssClass = 'select2--annotedLabels ' + parent.dropdownCssClass;

    return parent;
  }

  /**
   * {@inheritdoc}
   */
  protected convertBackendItem(item: NormalizedAttribute): object {
    return {
      id: item.code,
      text: i18n.getLabel(item.labels, UserContext.get('catalogLocale'), item.code),
      group: {
        text: (
          item.group ?
            i18n.getLabel(
              this.attributeGroups[item.group].labels,
              UserContext.get('catalogLocale'),
              this.attributeGroups[item.group],
            ) : ''
        ),
      },
    };
  }

  /**
   * {@inheritdoc}
   *
   * Removes the useless catalogLocale field, and adds localizable, is_locale_specific and scopable filters.
   */
  protected select2Data(term: string, page: number): object {
    return {
      localizable: false,
      is_locale_specific: false,
      scopable: false,
      search: term,
      types: this.config.types.join(','),
      options: {
        limit: this.resultsPerPage,
        page,
      },
    };
  }

  /**
   * {@inheritdoc}
   *
   * Has been overrode because translations should be handle front side.
   * Translates messages.
   */
  protected getFieldErrors(errors: { [index: string]: { message: string, messageParams: any } }) {
    Object.keys(errors).map((index) => {
      errors[index].message = __(errors[index].message, errors[index].messageParams);
    });
    return BaseSimpleSelect.prototype.getFieldErrors.apply(this, arguments);
  }

  /**
   * Formats and updates list of items
   *
   * @param {object} item
   *
   * @return {string}
   */
  private onGetResult(item: { text: string, group: { text: string } }): string {
    return this.lineView({item});
  }
}

export = SimpleSelectAttribute;
