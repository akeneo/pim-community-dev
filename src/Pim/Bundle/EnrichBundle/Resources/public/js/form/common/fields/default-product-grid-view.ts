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
}

class DefaultProductGridView extends BaseSelect {
  /**
   * {@inheritdoc}
   */
  configure() {
    return $.when(
      BaseSelect.prototype.configure.apply(this, arguments),
      FetcherRegistry.getFetcher('datagrid-view').fetchAll({ alias: 'product-grid' })
        .then((datagridViews: InterfaceNormalizedDatagridView[]) => {
          this.config.choices = datagridViews;
        })
    );
  }

  /**
   * {@inheritdoc}
   */
  renderInput() {
    this.filterDatagridViews();

    return BaseSelect.prototype.renderInput.apply(this, arguments);
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
  isVisible(): boolean {
    this.filterDatagridViews();

    return this.config.choices.length > 0;
  }

  /**
   * {@inheritdoc}
   */
  getDefaultLabel(): string {
    return __('pim_datagrid.view_selector.default_view');
  }

  /**
   * Filters the datagrid views to get only the ones of the edited user
   */
  filterDatagridViews(): void {
    const userId = this.getFormData().meta.id;
    this.config.choices = this.config.choices.filter((datagridView: InterfaceNormalizedDatagridView) => {
      return datagridView.owner_id === userId;
    });
  }
}

export = DefaultProductGridView;
