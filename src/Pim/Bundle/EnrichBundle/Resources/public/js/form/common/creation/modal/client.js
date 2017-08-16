'use strict';

define(
    [
        'pim/form/common/creation/modal',
        'oro/messenger',
        'oro/mediator',
        'oro/translator'
    ],
    function (
        BaseModal,
        messenger,
        mediator,
        __
    ) {
        return BaseModal.extend({
            /**
             * {@inheritdoc}
             */
            confirmModal(modal, deferred) {
                this.save().done(() => {
                    modal.close();
                    modal.remove();
                    deferred.resolve();

                    messenger.notify('success', __(this.config.successMessage));
                    mediator.trigger('datagrid:doRefresh:' + this.config.gridName);
            });
            }
        });
    }
);
