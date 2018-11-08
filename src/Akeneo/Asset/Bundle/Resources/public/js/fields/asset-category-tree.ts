import * as $ from 'jquery';
const BaseSelect = require('pim/form/common/fields/select');
const FetcherRegistry = require('pim/fetcher-registry');
const UserContext = require('pim/user-context');

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InterfaceNormalizedCategory {
  code: string;
  labels: { [key:string] : string };
}

class AssetCategoryTree extends BaseSelect {
  /**
   * {@inheritdoc}
   */
  configure() {
    return $.when(
      BaseSelect.prototype.configure.apply(this, arguments),
      FetcherRegistry.getFetcher('asset-category').fetchAll()
        .then((categories: InterfaceNormalizedCategory[]) => {
          this.config.choices = categories;
        })
    );
  }

  /**
   * @{inheritdoc}
   */
  formatChoices(categories: InterfaceNormalizedCategory[]): { [key:string] : string } {
    return categories.reduce((result: { [key:string] : string }, category: InterfaceNormalizedCategory) => {
      const label = category.labels[UserContext.get('user_default_locale')];
      result[category.code] = label !== undefined ? label : '[' + category.code + ']';

      return result;
    }, {});
  }
}

export = AssetCategoryTree;
