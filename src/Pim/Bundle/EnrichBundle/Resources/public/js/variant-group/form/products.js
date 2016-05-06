'use strict';

/**
 * Module used to display the product datagrid in a variant group
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
        'oro/translator',
        'pim/form',
        'pim/fetcher-registry',
        'pim/user-context',
        'pim/common/grid'
    ],
    function (
        __,
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
                    label: __('pim_enrich.form.variant_group.tab.products.title')
                });

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.productGroupGrid) {
                    this.productGroupGrid = new Grid(
                        'product-variant-group-grid',
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
