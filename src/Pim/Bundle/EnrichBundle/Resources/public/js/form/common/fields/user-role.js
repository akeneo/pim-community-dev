/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'jquery',
    'pim/form/common/fields/select',
    'pim/fetcher-registry'
],
function (
    $,
    BaseSelect,
    FetcherRegistry
) {
    return BaseSelect.extend({
        /**
         * {@inheritdoc}
         *
         * Get all the user groups but the default one
         */
        configure: function () {
            return $.when(
                BaseSelect.prototype.configure.apply(this, arguments),
                FetcherRegistry.getFetcher('user-role').fetchAll()
                    .then(function (userRoles) {
                        this.config.choices = userRoles;
                    }.bind(this))
            );
        },

        /**
         * @param {Array} userRoles
         */
        formatChoices: function (userRoles) {
            return userRoles.reduce((result, userRole) => {
                result[userRole.role] = userRole.label;
                return result;
            }, {});
        }
    });
});
