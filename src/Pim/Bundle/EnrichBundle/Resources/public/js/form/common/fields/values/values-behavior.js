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
         * {@inheritdoc}
         */
        writeValue(value) {
            const values = this.getFormData().values;
            values[this.fieldName] = [{scope: null, locale: null, data: value}];
            this.setData({values: values});
        },

        /**
         * {@inheritdoc}
         */
        readValue() {
            const standardValues = this.getFormData().values[this.fieldName];

            return undefined === standardValues ? standardValues : standardValues[0].data;
        }
    };
});
