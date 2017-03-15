'use strict';

/**
 * Module to save group type
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
        'underscore',
        'pim/saver/base',
        'module',
        'routing'
    ], function (
        _,
        BaseSaver,
        module,
        Routing
    ) {
        return _.extend({}, BaseSaver, {
            /**
             * {@inheritdoc}
             */
            getUrl: function (code) {
                return Routing.generate(this.url, { code: code });
            },

            /**
             * Sets the url
             *
             * @param {Sringt} url Route url
             */
            setUrl: function (url) {
                this.url = url;
                return this;
            }
        });
    }
);
