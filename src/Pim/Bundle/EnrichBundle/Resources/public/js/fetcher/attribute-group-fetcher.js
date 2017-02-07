'use strict';

/**
 * Attribute group fetcher
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
define([
    'jquery',
    'pim/base-fetcher',
    'routing'
], function (
    $,
    BaseFetcher,
    Routing
) {
    return BaseFetcher.extend({
        /**
         * Overrides base method, to send query using POST instead GET,
         * because the request URI can be too long.
         * TODO Should be deleted to set it back to GET.
         * SEE attribute fetcher
         *
         * {@inheritdoc}
         */
        getJSON: function (url, parameters) {
            return $.post(Routing.generate(url), parameters, null, 'json');
        }
    });
});
