import * as $ from 'jquery';
const BaseSelect = require('pim/form/common/fields/select');
const FetcherRegistry = require('pim/fetcher-registry');

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
type InterfaceNormalizedUserGroup = {
  meta: { default: boolean };
  name: string;
}

class UserGroupField extends BaseSelect {
  /**
   * {@inheritdoc}
   *
   * Get all the user groups but the default one
   */
  configure() {
    return $.when(
      BaseSelect.prototype.configure.apply(this, arguments),
      FetcherRegistry.getFetcher('user-group').fetchAll()
        .then((userGroups: InterfaceNormalizedUserGroup[]) => {
          this.config.choices = userGroups.filter((userGroup) => {
            return userGroup.meta.default !== true;
          });
        })
    );
  }

  /**
   * @param {Array} userGroups
   */
  formatChoices(userGroups: InterfaceNormalizedUserGroup[]): { [key:string] : string } {
    return userGroups.reduce((result: { [key:string] : string }, userGroup: InterfaceNormalizedUserGroup) => {
      result[userGroup.name] = userGroup.name;

      return result;
    }, {});
  }
}

export = UserGroupField
