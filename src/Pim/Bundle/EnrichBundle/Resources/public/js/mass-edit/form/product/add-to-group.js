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
        'jquery',
        'underscore',
        'oro/translator',
        'oro/messenger',
        'pim/i18n',
        'pim/user-context',
        'pim/mass-edit-form/product/operation',
        'pim/fetcher-registry',
        'pim/common/property',
        'pim/template/mass-edit/product/add-to-group'
    ],
    function (
        $,
        _,
        __,
        messenger,
        i18n,
        UserContext,
        BaseOperation,
        FetcherRegistry,
        propertyAccessor,
        template
    ) {
        return BaseOperation.extend({
            template: _.template(template),

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
                this.$el.html(this.template());
                this.renderExtensions();

                this.$el.find('input[name=group]').attr('disabled', this.readOnly ? 'disabled' : null);

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
                return _.findWhere(this.getFormData().actions, {field: 'group'});
            },

            /**
             * Checks there is at least one group selected to go to the next step
             */
            validate: function () {
                const data = this.getFormData();
                const groupsStr = propertyAccessor.accessProperty(data, 'group', '');
                const groups = groupsStr.split(',');
                this.setValue(groups);

                const hasUpdates = 0 !== groups.length;

                if (!hasUpdates) {
                    messenger.notify('error', __('pim_enrich.mass_edit.product.operation.add_to_group.no_update'));
                }

                return $.Deferred().resolve(hasUpdates);
            }
        });
    }
);
