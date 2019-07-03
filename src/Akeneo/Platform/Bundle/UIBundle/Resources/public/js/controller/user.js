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
                            let previousAvatarFilePath = user.avatar ? user.avatar.filePath : null;
                            form.on('pim_enrich:form:entity:post_save', (data) => {
                                const dataAvatarFilePath = data.avatar ? data.avatar.filePath : null;
                                if (data.user_default_locale !== previousUserLocale ||
                                    data.catalog_default_locale !== previousCatalogLocale ||
                                    data.catalog_default_scope !== previousCatalogScope ||
                                    data.default_category_tree !== previousDefaultCategoryTree ||
                                    dataAvatarFilePath !== previousAvatarFilePath
                                ) {
                                    previousUserLocale = data.user_default_locale;
                                    previousCatalogLocale = data.catalog_default_locale;
                                    previousCatalogScope = data.catalog_default_scope;
                                    previousDefaultCategoryTree = data.default_category_tree;
                                    previousAvatarFilePath = dataAvatarFilePath;
                                    // Prevent warning message (Firefox only)
                                    form.getExtension('state').collectAndRender();
                                    // Reload the page to reload new user interface variables
                                    location.reload();
                                }
                            });
                            form.on('pim_enrich:form:entity:pre_save', () => {
                                const data = form.getFormData();
                                data.current_password = null;
                                data.new_password = null;
                                data.new_password_repeat = null;
                                form.setData(data);
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
