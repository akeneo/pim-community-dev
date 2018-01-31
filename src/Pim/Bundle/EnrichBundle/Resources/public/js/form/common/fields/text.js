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
    'pim/template/form/common/fields/text'
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
            'keyup input': function (event) {
                this.errors = [];
                this.updateModel(this.getFieldValue(event.target));
                // Text fields don't trigger form render because there is no case of dependency with other fields.
                // Also, the fact the form is rendered when the focus is lost causes issues with other events triggering
                // (e.g. click on another field or on a button).
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
