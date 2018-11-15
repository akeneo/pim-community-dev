'use strict';

/**
 * Module used to display the product datagrid in a group
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
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
            initialize: function (config) {
                this.config = config.config;

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.trigger('tab:register', {
                    code: this.code,
                    label: __(this.config.label)
                });

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.productGroupGrid) {
                    this.productGroupGrid = new Grid(
                        this.config.gridId,
                        {
                            locale: UserContext.get('catalogLocale'),
                            currentGroup: this.getFormData().meta.id,
                            id: this.getFormData().meta.id,
                            selection: this.getFormData().products,
                            selectionIdentifier: 'identifier'
                        }
                    );

                    this.productGroupGrid.on('grid:selection:updated', function (selection) {
                        this.setData('products', selection);
                    }.bind(this));

                    this.getRoot().on('pim_enrich:form:entity:post_fetch', () => {
                        const shouldRefresh = this.code === this.getParent().getCurrentTab()
                        if (shouldRefresh) this.productGroupGrid.refresh();
                    });
                }

                this.$el.empty().append(this.productGroupGrid.render().$el);

                this.renderExtensions();
            }
        });
    }
);
