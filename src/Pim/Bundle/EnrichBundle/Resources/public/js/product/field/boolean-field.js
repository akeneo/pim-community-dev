'use strict';
/**
 * Boolean field
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    ['pim/field', 'underscore', 'text!pim/template/product/field/boolean', 'bootstrap.bootstrapswitch'],
    function (Field, _, fieldTemplate) {
    return Field.extend({
        fieldTemplate: _.template(fieldTemplate),
        fieldType: 'boolean',
        events: {
            'change input[type="checkbox"]:first': 'updateModel'
        },
        renderInput: function (context) {
            return this.fieldTemplate(context);
        },
        postRender: function () {
            this.$('.switch').bootstrapSwitch();
        },
        updateModel: function () {
            var data = this.$('input[type="checkbox"]').get(0).checked;

            this.setCurrentValue(data);
        }
    });
});
