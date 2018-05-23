'use strict';

/**
 * Allow to search a product with family variant in order to update it
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'oro/messenger',
        'pim/mass-edit-form/product/operation',
        'pim/common/property',
        'pim/template/mass-edit/product/update-entity-with-family-variant-parent'
    ],
    function (
        $,
        _,
        __,
        messenger,
        BaseOperation,
        propertyAccessor,
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
            },

            /**
             * Checks there is one product model selected to go to the next step
             */
            validate: function () {
                const data = this.getFormData();
                const productModelCode = propertyAccessor.accessProperty(data, 'actions.0.value', null);

                const hasUpdate = null !== productModelCode;

                if (!hasUpdate) {
                    messenger.notify(
                        'error',
                        __('pim_enrich.mass_edit.product.operation.add_to_existing_product_model.no_update')
                    );
                }

                return $.Deferred().resolve(hasUpdate);
            }
        });
    }
);
