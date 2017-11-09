/**
 * Use this logic in fields manipulating values in standard format.
 * For the moment we don't need to read/write scopable and/or localizable values, so this logic is not
 * implemented yet.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([], () => {
    return {
        /**
         * Formats the value according to the standard format then store it by calling the original field's method.
         *
         * @param {Object} BaseField
         * @param {*} value
         */
        writeValue(BaseField, value) {
            BaseField.prototype.updateModel.call(this, [{scope: null, locale: null, data: value}]);
        },

        /**
         * Read a standard formatted value and returns its data.
         *
         * @param {Object} BaseField
         *
         * @returns {*}
         */
        readValue(BaseField) {
            const standardValues = BaseField.prototype.getModelValue.call(this);

            return undefined === standardValues ? undefined : standardValues[0].data;
        }
    };
});
