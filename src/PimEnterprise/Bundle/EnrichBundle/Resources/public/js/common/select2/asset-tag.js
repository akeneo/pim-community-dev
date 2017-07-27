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
            getConfig: function (initialValue) {
                return {
                    allowClear: true,
                    multiple: true,
                    is_creatable: false,
                    ajax: {
                        url: Routing.generate('pim_ui_ajaxentity_list', {
                            class: 'PimEnterprise\\Component\\ProductAsset\\Model\\Tag',
                            dataLocale: UserContext.get('uiLocale'),
                            isCreatable: false
                        }),
                        quietMillis: 250,
                        cache: true,
                        data: function (term, page) {
                            return {
                                search: term,
                                options: {
                                    limit: 20,
                                    page: page,
                                    locale: UserContext.get('uiLocale')
                                }
                            };
                        },
                        results: function (data) {
                            data.more = 20 === _.keys(data.results).length;

                            return data;
                        }
                    },
                    initSelection: function (element, callback) {
                        if (null !== initialValue) {
                            callback(_.map(initialValue, function (text, id) {
                                return {id: id, text: text};
                            }));
                        }
                    }
                }
            }
        }
});

