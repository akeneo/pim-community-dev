define(
    [
        'underscore',
        'jquery',
        'pim/controller/front',
        'pim/form-builder',
        'pim/user-context',
        'oro/mediator',
        'pim/page-title',
        'routing',
        'pim/fetcher-registry'
    ],
    function (_, $, BaseController, FormBuilder, UserContext, mediator, PageTitle, Routing, fetcherRegistry) {
        return BaseController.extend({
            config: {
                gridExtension: 'pim-product-index',
                gridName: 'product-grid'
            },

            /**
            * {@inheritdoc}
            */
            initialize(options) {
                this.config = Object.assign(this.config, options.config || {});

                return BaseController.prototype.initialize.apply(this, arguments);
            },

            /**
            * {@inheritdoc}
            */
            renderForm() {
                this.selectMenuTab();

                const { gridName, gridExtension } = this.config;
                fetcherRegistry.getFetcher('locale').clear();
                fetcherRegistry.getFetcher('datagrid-view').clear();

                return $.when(this.resetSequentialEdit(),
                    FormBuilder.build(gridExtension).then((form) => {
                        this.setupLocale();
                        this.setupMassEditAttributes();
                        form.setElement(this.$el).render({ gridName });

                        return form;
                    })
                );
            },

            /**
            * {@inheritdoc}
            */
            renderTemplate(content) {
                if (!this.active) {
                    return;
                }

                this.$el.html(content);
            },

            /**
             * Sends a request to reset the current sequential edit in the backend
             * @return {Promise} The remove request
             */
            resetSequentialEdit() {
                return $.get(Routing.generate('pim_enrich_mass_edit_action_sequential_edit_remove'));
            },

            /**
             * Get the locale from url and set to UserContext
             */
            setupLocale() {
                const locale = window.location.hash.split('?dataLocale=')[1];
                if (locale) {
                    UserContext.set('catalogLocale', locale);
                }
            },

            /**
             * Clear mass edit selected attributes
             */
            setupMassEditAttributes() {
                sessionStorage.setItem('mass_edit_selected_attributes', JSON.stringify([]));
            },

            /**
             * Select products menu tab
             */
            selectMenuTab() {
                mediator.trigger('pim_menu:highlight:tab', { extension: 'pim-menu-products' });
            }
        });
    }
);
