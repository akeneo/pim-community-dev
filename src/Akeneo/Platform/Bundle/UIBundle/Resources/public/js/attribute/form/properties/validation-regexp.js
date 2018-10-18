/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'pim/form/common/fields/text'
],
function (
    BaseField
) {
    return BaseField.extend({
        /**
         * {@inheritdoc}
         *
         * This field should be displayed only when the validation rule is set to "regular expression".
         */
        isVisible: function () {
            return 'regexp' === this.getFormData().validation_rule;
        }
    });
});
