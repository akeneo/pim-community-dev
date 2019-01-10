'use strict';

define([
        'underscore',
        'pim/controller/front',
        'pim/fetcher-registry',
        'pim/page-title',
        'pim/form-builder',
    ], function (
        _,
        BaseController,
        FetcherRegistry,
        PageTitle,
        FormBuilder,
    ) {
        return BaseController.extend({
            /**
             * {@inheritdoc}
             */
            renderForm: function (route) {
                return FetcherRegistry.getFetcher('user').fetch(
                    route.params.identifier,
                ).then((user) => {
                    if (!this.active) {
                        return;
                    }

                    PageTitle.set({ 'username': _.escape(user.username) });

                    return FormBuilder.build(user.meta.form)
                        .then((form) => {
                            this.on('pim:controller:can-leave', function (event) {
                                form.trigger('pim_enrich:form:can-leave', event);
                            });

                            let previousCatalogScope = user.catalog_default_scope;
                            let previousDefaultCategoryTree = user.default_category_tree;
                            let previousUserLocale = user.user_default_locale;
                            let previousCatalogLocale = user.catalog_default_locale;
                            form.on('pim_enrich:form:entity:post_save', (data) => {
                                if (data.user_default_locale !== previousUserLocale ||
                                    data.catalog_default_locale !== previousCatalogLocale ||
                                    data.catalog_default_scope !== previousCatalogScope ||
                                    data.default_category_tree !== previousDefaultCategoryTree
                                ) {
                                    previousUserLocale = data.user_default_locale;
                                    previousCatalogLocale = data.catalog_default_locale;
                                    previousCatalogScope = data.catalog_default_scope;
                                    previousDefaultCategoryTree = data.default_category_tree;
                                    // Reload the page to reload new language
                                    location.reload();
                                }
                            });

                            form.setData(user);
                            form.trigger('pim_enrich:form:entity:post_fetch', user);
                            form.setElement(this.$el).render();

                            return form;
                        });
                });
            }
        });
    }
)
