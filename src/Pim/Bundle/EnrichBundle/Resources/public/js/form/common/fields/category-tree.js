/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'jquery',
    'pim/form/common/fields/select',
    'pim/fetcher-registry',
    'pim/user-context'
],
function (
    $,
    BaseSelect,
    FetcherRegistry,
    UserContext
) {
    return BaseSelect.extend({
        /**
         * {@inheritdoc}
         */
        configure: function () {
            return $.when(
                BaseSelect.prototype.configure.apply(this, arguments),
                FetcherRegistry.getFetcher('category').fetchAll()
                    .then(function (categories) {
                        this.config.choices = categories;
                    }.bind(this))
            );
        },

        /**
         * @param {Array} categories
         */
        formatChoices: function (categories) {
            return categories.reduce((result, category) => {
                const label = category.labels[UserContext.get('user_default_locale')];
                result[category.code] = label !== undefined ? label : '[' + category.code + ']';
                return result;
            }, {});
        }
    });
});
