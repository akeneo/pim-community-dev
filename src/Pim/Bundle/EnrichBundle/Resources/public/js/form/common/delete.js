'use strict';
/**
 * Delete extension
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/form',
        'text!pim/template/form/delete',
        'oro/navigation',
        'oro/loading-mask',
        'routing',
        'pim/dialog'
    ],
    function (
        _,
        __,
        BaseForm,
        template,
        Navigation,
        LoadingMask,
        Routing,
        Dialog
    ) {
        return BaseForm.extend({
            tagName: 'button',
            className: 'AknButton AknButton--important AknButton--withIcon AknTitleContainer-rightButton delete',
            template: _.template(template),
            events: {
                'click': 'delete'
            },

            /**
             * The remover should be injected / overridden by the concrete implementation
             * It is an object that define a remove function
             */
            remover: {
                remove: function () {
                    throw 'Remove function should be implemented in remover';
                }
            },

            /**
             * @param {Object} meta
             */
            initialize: function (meta) {
                this.config = _.extend({}, {
                    trans: {
                        title: 'confirmation.remove.item',
                        content: 'pim_enrich.confirmation.delete_item',
                        success: 'flash.item.removed',
                        fail: 'error.removing.item'
                    },
                    redirect: 'oro_default'
                }, meta.config);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({'__': __}));
                this.delegateEvents();

                return this;
            },

            /**
             * Open a dialog to ask the user to confirm the deletion
             */
            delete: function () {
                Dialog.confirm(
                    __(this.config.trans.title),
                    __(this.config.trans.content),
                    this.doDelete.bind(this)
                );
            },

            /**
             * Send a request to the backend in order to delete the element
             */
            doDelete: function () {
                var loadingMask = new LoadingMask();
                loadingMask.render().$el.appendTo(this.getRoot().$el).show();
                var navigation = Navigation.getInstance();

                this.remover.remove(this.getIdentifier())
                    .done(function () {
                        navigation.addFlashMessage('success', __(this.config.trans.success));
                        navigation.setLocation(Routing.generate(this.config.redirect));
                    }.bind(this))
                    .fail(function (xhr) {
                        var message = xhr.responseJSON && xhr.responseJSON.message ?
                            xhr.responseJSON.message :
                            __(this.config.trans.failed);

                        navigation.addFlashMessage('error', message);
                        navigation.afterRequest();
                    })
                    .always(function () {
                        loadingMask.hide().$el.remove();
                    });
            },

            /**
             * Get the current form identifier
             *
             * @return {String}
             */
            getIdentifier: function () {
                return this.getFormData().code;
            }
        });
    }
);
