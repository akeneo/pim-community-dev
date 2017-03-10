'use strict';

define(
    [
        'underscore',
        'pim/controller/base',
        'pim/form-builder',
        'pim/fetcher-registry',
        'pim/user-context',
        'pim/dialog',
        'pim/page-title',
        'pim/error'
    ],
    function (_, BaseController, FormBuilder, FetcherRegistry, UserContext, Dialog, PageTitle, Error) {
        return BaseController.extend({
            /**
             * {@inheritdoc}
             */
            renderRoute: function (route) {
                var type = route.name.indexOf('pim_importexport_import') === -1 ? 'export' : 'import';
                var mode = route.name.indexOf('_profile_show') === -1 ? 'edit' : 'show';

                return FetcherRegistry.getFetcher('job-instance-' + type).fetch(route.params.code)
                    .then(function (jobInstance) {
                        if (!this.active) {
                            return;
                        }

                        PageTitle.set({'jobInstance.label': _.escape(jobInstance.label) });

                        FormBuilder.build(jobInstance.meta.form + '-' + mode)
                            .then(function (form) {
                                form.setData(jobInstance);
                                form.trigger('pim_enrich:form:entity:post_fetch', jobInstance);
                                form.setElement(this.$el).render();
                            }.bind(this));
                    }.bind(this))
                .fail(function (response) {
                    var errorView = new Error(response.responseJSON.message, response.status);
                    errorView.setElement(this.$el).render();
                });
            }
        });
    }
);
