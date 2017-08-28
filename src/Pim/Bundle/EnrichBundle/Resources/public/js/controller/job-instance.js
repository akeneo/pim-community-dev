'use strict';

define(
    [
        'underscore',
        'oro/translator',
        'pim/controller/front',
        'pim/form-builder',
        'pim/fetcher-registry',
        'pim/user-context',
        'pim/dialog',
        'pim/page-title'
    ],
    function (_, __, BaseController, FormBuilder, FetcherRegistry, UserContext, Dialog, PageTitle) {
        return BaseController.extend({
            /**
             * {@inheritdoc}
             */
            renderForm: function (route) {
                var type = route.name.indexOf('pim_importexport_import') === -1 ? 'export' : 'import';
                var mode = route.name.indexOf('_profile_show') === -1 ? 'edit' : 'show';

                return FetcherRegistry.getFetcher('job-instance-' + type).fetch(route.params.code, {cached: false})
                    .then((jobInstance) => {
                        if (!this.active) {
                            return;
                        }

                        PageTitle.set({'job.label': _.escape(jobInstance.label) });

                        return FormBuilder.build(jobInstance.meta.form + '-' + mode)
                            .then((form) => {
                                this.on('pim:controller:can-leave', (event) => {
                                    form.trigger('pim_enrich:form:can-leave', event);
                                });
                                form.setData(jobInstance);
                                form.trigger('pim_enrich:form:entity:post_fetch', jobInstance);
                                form.setElement(this.$el).render();

                                return form;
                            });
                    });
            }
        });
    }
);
