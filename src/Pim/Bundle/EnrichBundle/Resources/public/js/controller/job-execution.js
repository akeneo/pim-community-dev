'use strict';

define(
    [
        'underscore',
        'oro/translator',
        'pim/controller/base',
        'pim/form-builder',
        'pim/fetcher-registry',
        'pim/user-context',
        'pim/dialog',
        'pim/page-title',
        'pim/error'
    ],
    function (_, __, BaseController, FormBuilder, FetcherRegistry, UserContext, Dialog, PageTitle, Error) {
        return BaseController.extend({
            /**
             * {@inheritdoc}
             */
            renderRoute: function (route) {
                return FetcherRegistry.getFetcher('job-execution').fetch(
                        route.params.id, {id: route.params.id, cached: false}
                    ).then(function (jobExecution) {
                        if (!this.active) {
                            return;
                        }

                        FormBuilder.build('pim-job-execution-form')
                            .then(function (form) {
                                this.on('pim:controller:can-leave', function (event) {
                                    form.trigger('pim_enrich:form:can-leave', event);
                                });
                                form.setData(jobExecution);
                                form.getRoot().trigger('pim-job-execution-form:start-auto-update', jobExecution);

                                this.on('pim-controller:job-execution:remove', function () {
                                    form.getRoot().trigger('pim-job-execution-form:stop-auto-update');
                                }.bind(this));
                                form.setElement(this.$el).render();
                            }.bind(this));
                    }.bind(this))
                .fail(function (response) {
                    var message = response.responseJSON ? response.responseJSON.message : __('error.common');

                    var errorView = new Error(message, response.status);
                    errorView.setElement(this.$el).render();
                });
            },

            remove: function () {
                this.trigger('pim-controller:job-execution:remove');

                BaseController.prototype.remove.apply(this, arguments);
            }
        });
    }
);
