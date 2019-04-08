'use strict';

/**
 * Text view extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
    'underscore',
    'pim/job/common/edit/field/field',
    'pim/template/export/common/edit/field/text',
    'edition/provider'
], function (
    _,
    BaseField,
    fieldTemplate,
    EditionProvider
) {
    return BaseField.extend({
        fieldTemplate: _.template(fieldTemplate),
        events: {
            'change input': 'updateState'
        },

        /**
         * Get the field dom value
         *
         * @return {string}
         */
        getFieldValue: function () {
            return this.$('input').val();
        },

        render: function() {
            if (EditionProvider.isCloud()) {
                this.$el.hide();
            }
        }
    });
});
