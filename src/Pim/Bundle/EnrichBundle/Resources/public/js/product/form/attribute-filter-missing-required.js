 'use strict';
/**
 * Filter returning only values missing required values.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/provider/to-fill-field-provider',
        'pim/user-context'
    ],
    function (
        $,
        _,
        __,
        BaseForm,
        toFillFieldProvider,
        UserContext
    ) {
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
                const scope = UserContext.get('catalogScope');
                const locale = UserContext.get('catalogLocale');

                const fieldsToFill = toFillFieldProvider.getMissingRequiredFields(this.getFormData(), scope, locale);
                const valuesToFill = _.pick(values, fieldsToFill);

                return $.Deferred().resolve(valuesToFill).promise();
            }
        });
    }
);
