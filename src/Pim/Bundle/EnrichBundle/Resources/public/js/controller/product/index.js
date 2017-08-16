define(
    [
        'underscore',
        'jquery',
        'pim/controller/base',
        'pim/form-builder',
        'pim/user-context',
        'oro/mediator'
    ],
    function (_, $, BaseController, FormBuilder, UserContext, mediator) {
        return BaseController.extend({
            /**
            * {@inheritdoc}
            */
            renderRoute() {
                this.selectMenuTab();

                return FormBuilder.build('pim-product-index').then((form) => {
                    this.setupLocale();
                    // Move somewhere else
                    this.setupMassEditAttributes();
                    form.setElement(this.$el).render();
                });
            },

            renderTemplate: function (content) {
                if (!this.active) return;
                this.$el.html(content);
            },

            setupLocale() {
                const locale = window.location.hash.split('?dataLocale=')[1];
                if (locale) UserContext.set('catalogLocale', locale);
            },

            setupMassEditAttributes() {
                sessionStorage.setItem('mass_edit_selected_attributes', JSON.stringify([]));
            },

            selectMenuTab() {
                mediator.trigger('pim_menu:highlight:tab', { extension: 'pim-menu-products' });
            }
        });
    }
);
