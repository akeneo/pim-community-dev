 'use strict';
/**
 * Idle filter used as default.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

define(
    ['jquery', 'oro/translator', 'pim/form'],
    function ($, __, BaseForm) {
        return BaseForm.extend({
            /**
             * @returns {String}
             */
            getCode() {
                return 'all';
            },

            /**
             * @returns {String}
             */
            getLabel() {
                return __('pim_enrich.entity.product.module.attribute_filter.all');
            },

            /**
             * @returns {Boolean}
             */
            isVisible() {
                return true;
            },

            /**
             * @param {Object} values
             *
             * @returns {Promise}
             */
            filterValues(values) {
                return $.Deferred().resolve(values).promise();
            }
        });
    }
);
