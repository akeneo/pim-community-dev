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

            reset: function () {
                this.setValue(null);
            },

            render: function () {
                FetcherRegistry.getFetcher('variant-group').fetchAll().then(function (groups) {
                    this.$el.html(this.template({
                        value: this.getValue(),
                        groups: groups,
                        i18n: i18n,
                        readOnly: this.readOnly,
                        locale: UserContext.get('uiLocale')
                    }));

                    this.$('.groups').select2();
                }.bind(this));

                return this;
            },

            updateModel: function (event) {
                this.setValue(event.target.value);
            },

            setValue: function (group) {
                var data = this.getFormData();

                data.actions = [{
                    field: 'variant_group',
                    value: group
                }];

                this.setData(data);
            },

            getValue: function () {
                var action = _.findWhere(this.getFormData().actions, {field: 'variant_group'})

                return action ? action.value : null;
            }
        });
    }
);
