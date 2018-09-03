'use strict';
/**
 * Change family operation
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
        'pim/common/select2/family',
        'pim/template/mass-edit/product/change-family',
        'pim/initselect2'
    ],
    function (
        _,
        __,
        BaseOperation,
        Select2Configurator,
        template,
        initSelect2
    ) {
        return BaseOperation.extend({
            template: _.template(template),
            events: {
                'change .family': 'updateModel'
            },

            /**
             * {@inheritdoc}
             */
            reset: function () {
                this.setValue(null);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    readOnly: this.readOnly,
                    value: this.getValue(),
                    label: __('pim_enrich.entity.family.uppercase_label')
                }));

                var options = Select2Configurator.getConfig(this.getValue());

                initSelect2.init(this.$('.family'), options);

                return this;
            },

            /**
             * Update the form model from a dom event
             *
             * @param {event} event
             */
            updateModel: function (event) {
                this.setValue(event.target.value);
            },

            /**
             * update the form model
             *
             * @param {string} family
             */
            setValue: function (family) {
                var data = this.getFormData();

                data.actions = [{
                    field: 'family',
                    value: family
                }];

                this.setData(data);
            },

            /**
             * Get the current model value
             *
             * @return {string}
             */
            getValue: function () {
                var action = _.findWhere(this.getFormData().actions, {field: 'family'})

                return action ? action.value : null;
            }
        });
    }
);
