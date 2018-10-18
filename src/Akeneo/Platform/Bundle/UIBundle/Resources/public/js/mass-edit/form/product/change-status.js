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

            /**
             * {@inheritdoc}
             */
            reset: function () {
                this.setValue(false);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    value: this.getValue(),
                    readOnly: this.readOnly,
                    labels: {
                        on: __('pim_common.yes'),
                        off: __('pim_common.no'),
                        field: __('pim_enrich.mass_edit.product.operation.change_status.field')
                    }
                }));

                this.$('.switch').bootstrapSwitch();

                return this;
            },

            /**
             * Update the form model from a dom event
             *
             * @param {event} event
             */
            updateModel: function (event) {
                this.setValue(event.target.checked);
            },

            /**
             * update the form model
             *
             * @param {string} family
             */
            setValue: function (enabled) {
                var data = this.getFormData();

                data.actions = [{
                    field: 'enabled',
                    value: enabled
                }];

                this.setData(data);
            },

            /**
             * Get the current model value
             *
             * @return {string}
             */
            getValue: function () {
                var action = _.findWhere(this.getFormData().actions, {field: 'enabled'})

                return action ? action.value : null;
            }
        });
    }
);
