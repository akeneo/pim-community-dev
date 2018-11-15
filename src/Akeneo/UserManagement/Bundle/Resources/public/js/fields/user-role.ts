import * as $ from 'jquery';
const BaseSelect = require('pim/form/common/fields/select');
const FetcherRegistry = require('pim/fetcher-registry');

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
type InterfaceNormalizedUserRole = {
    role: string;
    label: string;
}

class UserRoleField extends BaseSelect {
  /**
   * {@inheritdoc}
   */
  configure() {
    return $.when(
      BaseSelect.prototype.configure.apply(this, arguments),
      FetcherRegistry.getFetcher('user-role').fetchAll()
        .then((userRoles: InterfaceNormalizedUserRole[]) => {
          this.config.choices = userRoles;
        })
    );
  }

  /**
   * {@inheritdoc}
   */
  formatChoices(userRoles: InterfaceNormalizedUserRole[]): { [key: string]: string } {
    return userRoles.reduce((result: { [key: string]: string }, userRole: InterfaceNormalizedUserRole) => {
      result[userRole.role] = userRole.label;

      return result;
    }, {});
  }
}

export = UserRoleField
