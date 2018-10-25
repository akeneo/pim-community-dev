/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'jquery',
    'underscore',
    'pim/form/common/fields/field',
    'pim/template/form/common/fields/textarea'
],
function (
    $,
    _,
    BaseField,
    template
) {
    return BaseField.extend({
        template: _.template(template),
        events: {
            'keyup textarea': function (event) {
                this.errors = [];
                this.updateModel(this.getFieldValue(event.target));
            }
        },

        /**
         * {@inheritdoc}
         */
        renderInput: function (templateContext) {
            return this.template(_.extend(templateContext, {
                value: this.getModelValue()
            }));
        },

        /**
         * {@inheritdoc}
         */
        getFieldValue: function (field) {
            return $(field).val();
        }
    });
});
