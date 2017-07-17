/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'underscore',
    'pim/remover/base',
    'routing'
], function (
    _,
    BaseRemover,
    Routing
) {
    return _.extend({}, BaseRemover, {
        /**
         * {@inheritdoc}
         */
        getUrl: function (code) {
            return Routing.generate(__moduleConfig.url, {code: code});
        }
    });
});
