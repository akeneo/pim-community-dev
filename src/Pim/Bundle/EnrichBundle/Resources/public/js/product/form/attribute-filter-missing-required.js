 'use strict';
/**
 * Filter returning only values missing required values.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

define(
    ['underscore', 'oro/translator', 'pim/form', 'pim/provider/to-fill-field-provider'],
    function (_, __, BaseForm, toFillFieldProvider) {
        return BaseForm.extend({
            /**
             * @returns {String}
             */
            getCode() {
                return 'missing_required';
            },

            /**
             * @returns {String}
             */
            getLabel() {
                return __('pim_enrich.form.product.tab.attributes.attribute_filter.missing_required');
            },

            /**
             * @param {Object} values
             *
             * @returns {Promise}
             */
            filterValues(values) {
                return toFillFieldProvider.getFields(this.getRoot(), values)
                    .then(fieldCodes => _.pick(values, fieldCodes));
            }
        });
    }
);
