'use strict';

/**
 * asset fetcher
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
define([
    'jquery',
    'underscore',
    'pim/base-fetcher',
    'routing'
], function (
    $,
    _,
    BaseFetcher,
    Routing
) {
    return BaseFetcher.extend({
        /**
         * Overrides base method, to send query using POST instead GET,
         * because the request URI can be too long.
         * SEE attribute fetcher
         *
         * {@inheritdoc}
         */
        getJSON: function (url, parameters) {
            return $.post(Routing.generate(url), parameters, null, 'json');
        }
    });
});
