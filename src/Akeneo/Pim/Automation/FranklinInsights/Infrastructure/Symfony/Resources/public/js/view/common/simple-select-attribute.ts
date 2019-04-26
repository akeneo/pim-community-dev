/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import NormalizedAttribute from 'pim/model/attribute';
import NormalizedAttributeGroup from 'pim/model/attribute-group';
import * as _ from 'underscore';

const __ = require('oro/translator');
const BaseSimpleSelect = require('pim/form/common/fields/simple-select-async');
const i18n = require('pim/i18n');
const UserContext = require('pim/user-context');
const FetcherRegistry = require('pim/fetcher-registry');
const LineTemplate = require('pim/template/attribute/attribute-line');

/**
 * Attributes simple select
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class SimpleSelectAttribute extends BaseSimpleSelect {
  private readonly lineView = _.template(LineTemplate);
  private attributeGroups: { [pimAttributeGroupCode: string]: NormalizedAttributeGroup } = {};

  /**
   * {@inheritdoc}
   */
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
        .then((attributeGroups: { [pimAttributeGroupCode: string]: NormalizedAttributeGroup }) => {
          this.attributeGroups = attributeGroups;
        }),
    );
  }

  /**
   * {@inheritdoc}
   */
  public postRender(templateContext: any) {
    super.postRender(templateContext);

    this.trigger('post_render');
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
      type: item.type,
    };
  }

  /**
   * {@inheritdoc}
   *
   * Removes the useless catalogLocale field, and adds localizable, is_locale_specific and scopable filters.
   */
  protected select2Data(term: string, page: number): object {
    const result: any = {
      localizable: false,
      is_locale_specific: false,
      scopable: false,
      search: term,
      types: this.config.types.join(','),
      options: {
        limit: this.resultsPerPage,
        page,
        locale: UserContext.get('catalogLocale'),
      },
    };

    if (this.config.families) {
      result.families = this.config.families;
    }

    return result;
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
