'use strict';

/**
 * Module to remove family variant
 *
 * @author    Samir Boulil <samir.boulil@gmail.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
    }
);
