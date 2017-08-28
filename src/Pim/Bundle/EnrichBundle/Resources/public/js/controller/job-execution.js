'use strict';

define(
    [
        'underscore',
        'pim/controller/front',
        'pim/form-builder',
        'pim/fetcher-registry'
    ],
    function (_, BaseController, FormBuilder, FetcherRegistry) {
        return BaseController.extend({
            /**
             * {@inheritdoc}
             */
            renderForm: function (route) {
                return FetcherRegistry.getFetcher('job-execution').fetch(
                        route.params.id, {id: route.params.id, cached: false}
                    ).then((jobExecution) => {
                        if (!this.active) {
                            return;
                        }

                        return FormBuilder.build('pim-job-execution-form')
                            .then((form) => {
                                this.on('pim:controller:can-leave', function (event) {
                                    form.trigger('pim_enrich:form:can-leave', event);
                                });
                                form.setData(jobExecution);
                                form.getRoot().trigger('pim-job-execution-form:start-auto-update', jobExecution);

                                this.on('pim-controller:job-execution:remove', () => {
                                    form.getRoot().trigger('pim-job-execution-form:stop-auto-update');
                                });
                                form.setElement(this.$el).render();

                                return form;
                            });
                    });
            },

            remove: function () {
                this.trigger('pim-controller:job-execution:remove');

                BaseController.prototype.remove.apply(this, arguments);
            }
        });
    }
);
