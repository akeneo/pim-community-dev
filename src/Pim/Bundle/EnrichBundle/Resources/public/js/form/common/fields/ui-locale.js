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
         */
        configure: function () {
            return $.when(
                BaseSelect.prototype.configure.apply(this, arguments),
                FetcherRegistry.getFetcher('ui-locale').fetchAll()
                    .then(function (locales) {
                        this.config.choices = locales;
                    }.bind(this))
            );
        },

        /**
         * @param {Array} locales
         */
        formatChoices: function (locales) {
            return locales.reduce((result, locale) => {
                result[locale.code] = locale.label;
                return result;
            }, {});
        }
    });
});
