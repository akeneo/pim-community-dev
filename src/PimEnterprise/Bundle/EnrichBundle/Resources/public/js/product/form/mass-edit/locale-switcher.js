'use strict';

/**
 * Locale switcher extension for mass edit common attributes.
 * It's an override of the "locale switcher" extension since we need to display only editable locales.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'pim/product-edit-form/locale-switcher',
        'pim/fetcher-registry'
    ],
    function (
        $,
        _,
        BaseLocaleSwitcher,
        FetcherRegistry
    ) {
        return BaseLocaleSwitcher.extend({
            /**
             * {@inheritdoc}
             *
             * We override the original method to display only editable locales
             */
            getDisplayedLocales: function () {
                return $.when(
                    FetcherRegistry.getFetcher('permission').fetchAll(),
                    FetcherRegistry.getFetcher('locale').fetchAll()
                ).then(function (permissions, locales) {
                    var editableLocaleCodes = _.chain(permissions.locales)
                        .where({edit: true})
                        .pluck('code')
                        .value();

                    locales = _.filter(locales, function (locale) {
                        return _.contains(editableLocaleCodes, locale.code);
                    });

                    return locales;
                });
            }
        });
    }
);
