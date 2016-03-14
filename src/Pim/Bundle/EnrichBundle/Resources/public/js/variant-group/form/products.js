"use strict";

define([
        'pim/form',
        'pim/fetcher-registry',
        'pim/user-context',
        'pim/common/grid'
    ],
    function(
        BaseForm,
        FetcherRegistry,
        UserContext,
        Grid
    ) {
        return BaseForm.extend({
            className: 'products',

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.trigger('tab:register', {
                    code: this.code,
                    label: _.__('pim_enrich.form.variant_group.tab.products.title')
                });

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.productGroupGrid) {
                    this.productGroupGrid = new Grid(
                        'product-group-grid',
                        {
                            locale: UserContext.get('catalogLocale'),
                            currentGroup: this.getFormData().meta.id,
                            id: this.getFormData().meta.id,
                            selection: this.getFormData().products
                        }
                    );

                    this.productGroupGrid.on('grid:selection:updated', function (selection) {
                        this.setData('products', selection);
                    }.bind(this));

                    this.getRoot().on('pim_enrich:form:entity:post_fetch', function () {
                        this.productGroupGrid.refresh();
                    }.bind(this));
                }

                this.$el.empty().append(this.productGroupGrid.render().$el);

                this.renderExtensions();
            }
        });
    }
);
