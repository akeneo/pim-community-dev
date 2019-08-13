import * as $ from "jquery";
import BaseView = require('pimui/js/view/base');
import DefaultProductGridView = require('pimuser/js/fields/default-product-grid-view');
const Routing = require('routing');

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DefaultGridView extends BaseView {
  private datagridAliases: string[] = [];
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
      BaseView.prototype.configure.apply(this, arguments),
      $.get(Routing.generate('pim_datagrid_view_rest_types'))
        .then((datagridViewTypes: string[]) => {
          this.datagridAliases = datagridViewTypes;
        })
    );
  }

  /**
   * {@inheritdoc}
   */
  render() {
    this.$el.html('');
    this.datagridAliases.forEach((datagridAlias: string) => {
      const datagridViewSelector = new DefaultProductGridView({
        config: {
          datagridAlias: datagridAlias,
          fieldName: 'default_' + datagridAlias.replace(/-/g, '_') + '_view',
          label: 'pim_user_management.entity.user.properties.default_' + datagridAlias.replace(/-/g, '_') + '_view',
          readOnly: this.config.readOnly,
          choiceUrl: 'pim_datagrid_view_rest_index',
          placeholder: 'pim_datagrid.view_selector.default_view',
        }
      });
      datagridViewSelector.configure().then(() => {
        datagridViewSelector.setParent(this);
        this.$el.append(datagridViewSelector.render().$el);
      });
    });

    return this;
  }
}

export = DefaultGridView
