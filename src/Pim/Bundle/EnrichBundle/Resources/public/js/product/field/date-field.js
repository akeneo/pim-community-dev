'use strict';
/**
 * Date field
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'pim/field',
        'underscore',
        'text!pim/template/product/field/date',
        'bootstrap.bootstrapsdatepicker'
    ],
    function (
        Field,
        _,
        fieldTemplate
    ) {
        return Field.extend({
            fieldTemplate: _.template(fieldTemplate),
            fieldType: 'date',
            events: {
                'change input': 'updateModel'
            },
            renderInput: function (context) {
                return this.fieldTemplate(context);
            },
            render: function () {
                Field.prototype.render.apply(this, arguments);

                setTimeout(_.bind(function () {
                    this.$('.datepicker').datepicker();
                }, this), 0);
            },
            updateModel: function (event) {
                var data = event.currentTarget.value;
                this.setCurrentValue(data);
            }
        });
    }
);
