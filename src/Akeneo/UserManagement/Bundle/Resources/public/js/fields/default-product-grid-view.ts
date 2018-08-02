import * as $ from 'jquery';
const __ = require('oro/translator');
const BaseSelect = require('pim/form/common/fields/select');
const FetcherRegistry = require('pim/fetcher-registry');

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InterfaceNormalizedDatagridView {
  id: string;
  label: string;
  owner_id: number;
  type: string; // 'public'|'project'
  datagrid_alias: string;
}

class DefaultProductGridView extends BaseSelect {
  private config: any;

  constructor(options: {config: any}) {
    super(options);

    this.config = {...this.config, ...options.config};
  }

  /**
   * {@inheritdoc}
   */
  configure() {
    return $.when(
      BaseSelect.prototype.configure.apply(this, arguments),
      FetcherRegistry.getFetcher('datagrid-view').fetchAll({ alias: this.config.datagridAlias })
        .then((datagridViews: InterfaceNormalizedDatagridView[]) => {
          this.config.choices = datagridViews;
        })
    );
  }

  /**
   * {@inheritdoc}
   */
  formatChoices(datagridViews: InterfaceNormalizedDatagridView[]): { [key:string] : string } {
    return datagridViews.reduce((result: { [key:string] : string }, datagridView: InterfaceNormalizedDatagridView) => {
      result[datagridView.id] = datagridView.label;

      return result;
    }, {});
  }

  /**
   * {@inheritdoc}
   */
  getModelValue(): string {
    const value = BaseSelect.prototype.getModelValue.apply(this, arguments);

    return value !== undefined ? value + '' : value;
  }

  /**
   * {@inheritdoc}
   */
  getDefaultLabel(): string {
    return __('pim_datagrid.view_selector.default_view');
  }
}

export = DefaultProductGridView;
