'use strict';
/**
 * Add to group operation
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/i18n',
        'pim/user-context',
        'pim/mass-edit-form/product/operation',
        'pim/fetcher-registry',
        'pim/template/mass-edit/product/add-to-variant-group',
        'jquery.select2'
    ],
    function (
        _,
        __,
        i18n,
        UserContext,
        BaseOperation,
        FetcherRegistry,
        template
    ) {
        return BaseOperation.extend({
            template: _.template(template),
            events: {
                'change .groups': 'updateModel'
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
                FetcherRegistry.getFetcher('variant-group').fetchAll().then(function (groups) {
                    this.$el.html(this.template({
                        value: this.getValue(),
                        groups: groups,
                        i18n: i18n,
                        readOnly: this.readOnly,
                        locale: UserContext.get('uiLocale'),
                        label: __('pim_enrich.mass_edit.product.operation.add_to_variant_group.field')
                    }));

                    this.$('.groups').select2();
                }.bind(this));

                return this;
            },

            /**
             * Update the mass edit model
             *
             * @param {Event} event
             */
            updateModel: function (event) {
                const value = event.target.value !== '' ? event.target.value : null;

                this.setValue(value);
            },

            /**
             * Update the model after dom event triggered
             *
             * @param {string} group
             */
            setValue: function (group) {
                var data = this.getFormData();

                data.actions = [{
                    field: 'variant_group',
                    value: group
                }];

                this.setData(data);
            },

            /**
             * Get current value from mass edit model
             *
             * @return {string}
             */
            getValue: function () {
                var action = _.findWhere(this.getFormData().actions, {field: 'variant_group'})

                return action ? action.value : null;
            }
        });
    }
);
