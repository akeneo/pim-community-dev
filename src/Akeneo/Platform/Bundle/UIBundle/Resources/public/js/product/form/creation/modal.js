'use strict';

define([
    'underscore',
    'oro/translator',
    'oro/messenger',
    'pim/form/common/creation/modal'
], function (
    _,
    __,
    messenger,
    BaseModal
) {
    return BaseModal.extend({
        postSuccess(entity) {
            if (entity.meta.warning) {
                messenger.notify('warning', entity.meta.warning);
            }
            messenger.notify('success', __(this.config.successMessage));
        },
    });
});
