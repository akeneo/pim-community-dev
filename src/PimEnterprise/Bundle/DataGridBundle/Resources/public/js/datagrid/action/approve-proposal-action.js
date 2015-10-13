'use strict';

/**
 * Approve proposal action
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
define(
    ['jquery', 'oro/mediator', 'oro/datagrid/ajax-action', 'pim/form-modal'],
    function ($, mediator, AjaxAction, FormModal) {
        return AjaxAction.extend({
            /**
             * @inheritdoc
             */
            getMethod: function () {
                return 'POST';
            },

            /**
             * Override the default handler to trigger the popin to add comment
             *
             * @param action
             */
            _handleAjax: function (action) {
                var modalParameters = {
                    title:       'LE TITRE',
                    okText:      'OK',
                    cancelText:  'ANNULAY'
                };

                var formModal = new FormModal('pimee-proposal-add-comment-form', function (form) {
                    var deferred = $.Deferred();
                    var comment = form.getFormData().comment;
                    // TODO: Check for max char.
                    deferred.resolve();

                    return deferred;
                }, modalParameters);

                formModal.open()
                    .then(function(data) {
                        //mediator.trigger('pim_enrich:form:proposal:pre_approve', data.comment);
                        //console.log('OK !!! ', data);
                        //console.log('OK !!! ', action);
                        //console.log(action.getLink());
                        //console.log(action.getMethod());
                        //console.log(action.getActionParameters(data));
                        //console.log(action);
                        $.ajax({
                            url: action.getLink(),
                            method: action.getMethod(),
                            data: data,
                            context: action,
                            dataType: 'json',
                            error: action._onAjaxError,
                            success: action._onAjaxSuccess
                        });
                    }.bind(this))
                    .fail(function() {
                        console.log('CANCELED');
                    });

                //mediator.trigger('pim_enrich:form:proposal:pre_approve', action.model);
                //var deferred = $.Deferred();
                //
                //FormBuilder.build('pim-notification-comment').done(function (form) {
                //    var modal = new Backbone.BootstrapModal({
                //        modalOptions: {
                //            backdrop: 'static',
                //            keyboard: false
                //        },
                //        allowCancel: true,
                //        okCloses: false,
                //        title: _.__('pimee_workflow.product_draft.modal.accept_proposal'),
                //        content: '',
                //        cancelText: _.__('pimee_enrich.entity.product_draft.modal.cancel'),
                //        okText: _.__('pimee_enrich.entity.product_draft.modal.confirm')
                //    });
                //
                //    modal.open();
                //    form.setElement(modal.$('.modal-body')).render(
                //        {'title': _.__('pimee_enrich.entity.product_draft.modal.title_comment')}
                //    );
                //    modal.on('cancel', deferred.reject);
                //    modal.on('ok', function () {
                //        deferred.resolve($('.modal-body textarea').val());
                //        modal.close();
                //    }.bind(this));
                //}.bind(this));
                //
                //deferred.done(function(comment) {
                //    console.log('ACTION = ', action);
                //    console.log('COMMENT = ', comment);
                //    console.log('this = ', this);
                //    this.comment = comment;
                //    AjaxAction.prototype._handleAjax.apply(this, action);
                //}.bind(this));
            },

            /**
             * Override the default handler to trigger the event containing the new product data
             *
             * @param product
             */
            _onAjaxSuccess: function (product) {
                this.datagrid.collection.fetch();

                mediator.trigger('pim_enrich:form:proposal:post_approve:success', product);
            },

            /**
             * Override the default handler to avoid displaying the error modal and triggering our own event instead
             *
             * @param jqXHR
             */
            _onAjaxError: function (jqXHR) {
                this.datagrid.hideLoading();

                mediator.trigger('pim_enrich:form:proposal:post_approve:error', jqXHR.responseJSON.message);
            }
        });
    }
);
