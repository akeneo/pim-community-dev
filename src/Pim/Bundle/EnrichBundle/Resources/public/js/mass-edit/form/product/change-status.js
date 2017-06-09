'use strict';
/**
 * Change status operation
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/mass-edit-form/product/operation',
        'pim/template/mass-edit/product/change-status',
        'bootstrap.bootstrapswitch'
    ],
    function (
        _,
        __,
        BaseOperation,
        template
    ) {
        return BaseOperation.extend({
            template: _.template(template),
            events: {
                'change .switch': 'updateModel'
            },

            reset: function () {
                this.setValue(false);
            },

            render: function () {
                this.$el.html(this.template({
                    value: this.getValue(),
                    readOnly: this.readOnly,
                    labels: {
                        on: __('switch_on'),
                        off: __('switch_off')
                    }
                }));

                this.$('.switch').bootstrapSwitch();

                return this;
            },

            updateModel: function (event) {
                this.setValue(event.target.checked);
            },

            setValue: function (enabled) {
                var data = this.getFormData();

                data.actions = [{
                    field: 'enabled',
                    value: enabled
                }];

                this.setData(data);
            },

            getValue: function () {
                var action = _.findWhere(this.getFormData().actions, {field: 'enabled'})

                return action ? action.value : null;
            }
        });
    }
);
