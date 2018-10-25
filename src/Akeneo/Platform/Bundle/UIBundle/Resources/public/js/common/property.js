'use strict';

/**
 * Property accessor extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([], function () {
    return {
        /**
         * Access a property in an object
         *
         * @param {object} data
         * @param {string} path
         * @param {*}  defaultValue
         *
         * @return {*}
         */
        accessProperty: function (data, path, defaultValue) {
            defaultValue = defaultValue || null;
            var pathPart = path.split('.');

            if (undefined === data[pathPart[0]]) {
                return defaultValue;
            }

            return 1 === pathPart.length ?
                data[pathPart[0]] :
                this.accessProperty(data[pathPart[0]], pathPart.slice(1).join('.'), defaultValue);
        },

        /**
         * Update a property in an object
         *
         * @param {object} data
         * @param {string} path
         * @param {*}  value
         *
         * @return {*}
         */
        updateProperty: function (data, path, value) {
            var pathPart = path.split('.');

            data[pathPart[0]] = 1 === pathPart.length ?
                value :
                this.updateProperty(data[pathPart[0]], pathPart.slice(1).join('.'), value);

            return data;
        }
    };
});
