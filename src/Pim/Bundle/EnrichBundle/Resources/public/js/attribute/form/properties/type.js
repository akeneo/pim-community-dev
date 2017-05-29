/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'underscore',
    'oro/translator',
    'pim/form',
    'text!pim/template/attribute/tab/properties/select'
],
function (
    _,
    __,
    BaseForm,
    template
) {
    return BaseForm.extend({
        className: 'AknFieldContainer',
        template: _.template(template),
        fieldName: 'type',

        render: function () {
            var value = this.getFormData()[this.fieldName];
            var choices = {};
            choices[value] = __('pim_enrich.entity.attribute.type.' + value);

            this.$el.html(this.template({
                value: value,
                fieldName: this.fieldName,
                choices: choices,
                labels: {
                    field: __('pim_enrich.form.attribute.tab.properties.' + this.fieldName),
                    required: __('pim_enrich.form.required')
                },
                multiple: false
            }));

            this.$('select.select2').select2();

            this.renderExtensions();
            this.delegateEvents();
        }
    });
});
