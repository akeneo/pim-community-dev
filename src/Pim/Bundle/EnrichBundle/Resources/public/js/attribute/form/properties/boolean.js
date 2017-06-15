/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'underscore',
    'oro/translator',
    'pim/attribute-edit-form/properties/field',
    'pim/template/attribute/tab/properties/boolean'
],
function (
    _,
    __,
    BaseField,
    template
) {
    return BaseField.extend({
        template: _.template(template),

        /**
         * {@inheritdoc}
         */
        renderInput: function (templateContext) {
            if (!_.has(this.getFormData(), this.fieldName) && _.has(this.config, 'defaultValue')) {
                this.updateModel(this.config.defaultValue);
            }

            return this.template(_.extend(templateContext, {
                value: this.getFormData()[this.fieldName],
                labels: {
                    on: __('switch_on'),
                    off: __('switch_off')
                }
            }));
        },

        /**
         * {@inheritdoc}
         */
        postRender: function () {
            this.$('.switch').bootstrapSwitch();
        },

        /**
         * {@inheritdoc}
         */
        getFieldValue: function (field) {
            return $(field).is(':checked');
        }
    });
});
