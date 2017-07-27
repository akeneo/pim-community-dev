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
        'pim/template/mass-edit/product/add-to-group'
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
                'change .group': 'updateModel'
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
                FetcherRegistry.getFetcher('group').fetchAll().then(function (groups) {
                    this.$el.html(this.template({
                        value: this.getValue(),
                        groups: groups,
                        i18n: i18n,
                        readOnly: this.readOnly,
                        locale: UserContext.get('uiLocale'),
                        label: __('pim_enrich.mass_edit.product.operation.add_to_group.field')
                    }));
                }.bind(this));

                return this;
            },

            /**
             * Update the mass edit model
             *
             * @param {Event} event
             */
            updateModel: function (event) {
                this.transformValue(event.target.value, event.target.checked ? _.union : _.without);
            },

            /**
             * Update the model after dom event triggered
             *
             * @param {array} groups
             */
            setValue: function (groups) {
                var data = this.getFormData();

                data.actions = [{
                    field: 'groups',
                    value: groups
                }];

                this.setData(data);
            },

            /**
             * Transform dom event to proper group array
             *
             * @param {string}   group
             * @param {function} method
             */
            transformValue: function (group, method) {
                var value = this.getValue();

                this.setValue(method(value, [group]));
            },

            /**
             * Get current value from mass edit model
             *
             * @return {array}
             */
            getValue: function () {
                var action = _.findWhere(this.getFormData().actions, {field: 'groups'})

                return action ? action.value : null;
            }
        });
    }
);
