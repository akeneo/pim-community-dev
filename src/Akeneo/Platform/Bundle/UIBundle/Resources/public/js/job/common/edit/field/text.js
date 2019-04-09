'use strict';

/**
 * Text view extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
    'jquery',
    'underscore',
    'pim/job/common/edit/field/field',
    'pim/template/export/common/edit/field/text',
    'edition/provider'
], function (
    $,
    _,
    BaseField,
    fieldTemplate,
    editionProvider
) {
    return BaseField.extend({
        fieldTemplate: _.template(fieldTemplate),
        isCloud: null,
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

        render: () => {
            if (editionProvider.isCloud() === false) {
                BaseField.prototype.render.apply(this, arguments); // on pourra faire super
            }

            return this;
        }
    });
});
