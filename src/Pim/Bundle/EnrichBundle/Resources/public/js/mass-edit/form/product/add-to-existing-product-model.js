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
        'pim/template/mass-edit/product/add-to-existing-product-model'
    ],
    function (
        _,
        BaseOperation,
        template
    ) {
        return BaseOperation.extend({
            template: _.template(template),
            events: {
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.updateModel);

                return BaseOperation.prototype.configure.apply(this, arguments);
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

            /**
             * Updates the model to store action
             *
             * @param {Object} formData
             */
            updateModel: function (formData) {
                if (this.getParent().getCurrentOperation() === this.getCode()) {
                    formData.actions = [{
                        field: 'productModelCode',
                        value: formData.product_model
                    }];

                    this.setData(formData, {silent: true});
                }
            },

            /**
             * {@inheritdoc}
             */
            getValue: function () {
                const action = _.findWhere(this.getFormData().actions, { field: 'productModelCode' });

                return action ? action.value : null;
            }
        });
    }
);
