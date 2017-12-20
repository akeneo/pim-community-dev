'use strict';
/**
 * Change tags operation
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
        'pimee/common/select2/asset-tag',
        'pimee/template/mass-edit/asset/add-tag',
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
                'change .tags': 'updateModel'
            },

            /**
             * {@inheritdoc}
             */
            reset: function () {
                this.setValue([]);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    readOnly: this.readOnly,
                    value: this.getValue().join(','),
                    field: __(this.config.field),
                    emptySelectionLabel: __('pimee_enrich.mass_edit.asset.operation.add_tag.empty_selection')
                }));

                var options = Select2Configurator.getConfig(this.getValue());

                initSelect2.init(this.$('.tags'), options);

                return this;
            },

            /**
             * Update the form model from a dom event
             *
             * @param {event} event
             */
            updateModel: function (event) {
                this.setValue(event.target.value.split(','));
            },

            /**
             * update the form model
             *
             * @param {string} family
             */
            setValue: function (tags) {
                var data = this.getFormData();

                data.actions = [{
                    field: 'tags',
                    value: tags
                }];

                this.setData(data);
            },

            /**
             * Get the current model value
             *
             * @return {string}
             */
            getValue: function () {
                var action = _.findWhere(this.getFormData().actions, {field: 'tags'})

                return action ? action.value : null;
            }
        });
    }
);
