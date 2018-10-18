 'use strict';
/**
 * Filter attributes only at this level.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

define(
    ['jquery', 'underscore', 'oro/translator', 'pim/form'],
    function ($, _, __, BaseForm) {
        return BaseForm.extend({
            /**
             * @returns {String}
             */
            getCode() {
                return 'at-this-level';
            },

            /**
             * @returns {String}
             */
            getLabel() {
                return __('pim_enrich.entity.product.module.attribute_filter.at_this_level');
            },

            /**
             * @returns {Boolean}
             */
            isVisible() {
                const meta = this.getFormData().meta;

                return null !== meta.level && meta.level > 0;
            },

            /**
             * @param {Object} values
             *
             * @returns {Promise}
             */
            filterValues(values) {
                const valuesToFill = _.pick(values, this.getFormData().meta.attributes_for_this_level);

                return $.Deferred().resolve(valuesToFill).promise();
            }
        });
    }
);
