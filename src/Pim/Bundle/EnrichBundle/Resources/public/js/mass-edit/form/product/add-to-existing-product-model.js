'use strict';

/**
 * Add to existing product model operation
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'pim/mass-edit-form/product/operation',
        'pim/template/mass-edit/product/add-to-existing-product-model',
        'pim/user-context'
    ],
    function (
        _,
        BaseOperation,
        template,
        UserContext
    ) {
        return BaseOperation.extend({
            template: _.template(template),
            events: {
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    value: this.getValue(),
                    readOnly: this.readOnly
                }));

                BaseOperation.prototype.render.apply(this, arguments);

                return this;
            },

            updateModel: function (event) {
                this.setValue(event.target.value);
            },

            setValue: function (comment) {
                let data = this.getFormData();
                data.actions = [{
                    field: 'comment',
                    value: comment,
                    username: UserContext.get('username')
                }];
                this.setData(data);
            },

            getValue: function () {
                const action = _.findWhere(this.getFormData().actions, { field: 'comment' });

                return action ? action.value : null;
            }
        });
    }
);
