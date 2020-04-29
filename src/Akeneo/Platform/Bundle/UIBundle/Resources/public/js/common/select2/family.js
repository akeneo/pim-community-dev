'use strict';
/**
 * Get Select2 conf for families
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'pim/fetcher-registry',
        'pim/user-context',
        'pim/i18n',
        'routing'
    ],
    function (
        _,
        FetcherRegistry,
        UserContext,
        i18n,
        Routing
    ) {
        return {
            /**
             * Provide the config for a family select2 field
             *
             * @param {string} initialValue
             * @return {object}
             */
            getConfig: function (initialValue) {
                return {
                    allowClear: true,
                    ajax: {
                        url: Routing.generate('pim_enrich_family_rest_index'),
                        quietMillis: 250,
                        cache: true,
                        data: function (term, page) {
                            return {
                                search: term,
                                options: {
                                    limit: 20,
                                    page: page,
                                    locale: UserContext.get('catalogLocale')
                                }
                            };
                        },
                        results: function (families) {
                            var data = {
                                more: 20 === _.keys(families).length,
                                results: []
                            };
                            _.each(families, function (value, key) {
                                data.results.push({
                                    id: key,
                                    text: i18n.getLabel(
                                        value.labels,
                                        UserContext.get('catalogLocale'),
                                        value.code
                                    )
                                });
                            });

                            return data;
                        }
                    },
                    initSelection: function (element, callback) {
                        if (null !== initialValue) {
                            FetcherRegistry.getFetcher('family')
                                .fetch(initialValue)
                                .then(function (family) {
                                    callback({
                                        id: family.code,
                                        text: i18n.getLabel(
                                            family.labels,
                                            UserContext.get('catalogLocale'),
                                            family.code
                                        )
                                    });
                                });
                        }

                    }
                }
            }
        }
    });
