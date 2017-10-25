/**
 * Use this logic in fields manipulating values in standard format.
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
            values[this.fieldName] = {scope: null, locale: null, data: value};
            this.setData({values: values});
        },

        /**
         * {@inheritdoc}
         */
        readValue() {
            const standardValue = this.getFormData().values[this.fieldName];

            return undefined === standardValue ? standardValue : standardValue.data;
        }
    };
});
