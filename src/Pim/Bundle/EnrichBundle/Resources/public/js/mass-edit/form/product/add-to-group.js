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
        'pim/template/mass-edit/product/add-to-group',
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

            reset: function () {
                this.setValue([]);
            },

            render: function () {
                FetcherRegistry.getFetcher('group').fetchAll().then(function (groups) {
                    this.$el.html(this.template({
                        value: this.getValue(),
                        groups: groups,
                        i18n: i18n,
                        readOnly: this.readOnly,
                        locale: UserContext.get('uiLocale')
                    }));
                }.bind(this));

                return this;
            },

            updateModel: function (event) {
                this.transformValue(event.target.value, event.target.checked ? _.union : _.without);
            },

            setValue: function (groups) {
                var data = this.getFormData();

                data.actions = [{
                    field: 'groups',
                    value: groups
                }];

                this.setData(data);
            },

            transformValue: function (group, method) {
                var value = this.getValue();

                this.setValue(method(value, [group]));
            },

            getValue: function () {
                var action = _.findWhere(this.getFormData().actions, {field: 'groups'})

                return action ? action.value : null;
            }
        });
    }
);
