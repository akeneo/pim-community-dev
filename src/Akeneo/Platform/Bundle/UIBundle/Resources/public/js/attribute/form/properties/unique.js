/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'underscore',
    'pim/form/common/fields/boolean'
],
function (_, BaseField) {
    return BaseField.extend({
        /**
         * {@inheritdoc}
         *
         * This field should be editable only for certain attribute types.
         */
        isReadOnly: function () {
            return BaseField.prototype.isReadOnly.apply(this, arguments) ||
                !_.contains(this.config.activeForTypes, this.getRoot().getType());
        }
    });
});
