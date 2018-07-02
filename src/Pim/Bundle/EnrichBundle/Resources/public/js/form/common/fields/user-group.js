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
                FetcherRegistry.getFetcher('user-group').fetchAll()
                    .then(function (userGroups) {
                        this.config.choices = userGroups.filter((userGroup) => {
                            return userGroup.meta.default !== true;
                        });
                    }.bind(this))
            );
        },

        /**
         * @param {Array} userGroups
         */
        formatChoices: function (userGroups) {
            return userGroups.reduce((result, userGroup) => {
                result[userGroup.name] = userGroup.name;
                return result;
            }, {});
        }
    });
});
