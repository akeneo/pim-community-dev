/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'jquery',
    'underscore',
    'pim/form/common/fields/select',
    'pim/fetcher-registry'
],
function (
    $,
    _,
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
                FetcherRegistry.getFetcher('locale').fetchActivated()
                    .then(function (availableLocales) {
                        this.config.choices = availableLocales;
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
