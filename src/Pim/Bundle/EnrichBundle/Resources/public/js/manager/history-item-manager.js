'use strict';
/**
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    ['jquery', 'routing'],
    function ($, Routing) {
        return {
            /**
             * Saves a history item.
             *
             * @param {string} url
             * @param {Object} title
             */
            save: function (url, title) {
                return $.post(
                    Routing.generate('pim_enrich_navigation_history_rest_post'),
                    JSON.stringify({url: url, title: title}),
                    null,
                    'json'
                );
            }
        };
    }
);
