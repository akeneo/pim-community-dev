 /**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'pim/form/common/fields/available-locales'
],
function (
    BaseAvailableLocales
) {
    return BaseAvailableLocales.extend({
        /**
         * {@inheritdoc}
         *
         * This field shouldn't be displayed if the attribute is not locale specific.
         */
        isVisible: function () {
            return undefined !== this.getFormData().is_locale_specific && this.getFormData().is_locale_specific;
        }
    });
});
