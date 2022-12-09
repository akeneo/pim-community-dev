'use strict';

define(['underscore', 'oro/translator', 'oro/messenger', 'pim/form/common/creation/modal'], function (
  _,
  __,
  messenger,
  BaseModal
) {
  return BaseModal.extend({
    postSuccess(entity) {
      if (entity.meta.identifier_generator_warnings) {
        const normalizedWarnings = entity.meta.identifier_generator_warnings.map(warning => {
          return `${warning.path}: ${warning.message}`;
        });

        messenger.notify('warning', this.updateIdentifierWarningMessage, normalizedWarnings);
      }
      messenger.notify('success', __(this.config.successMessage));
    },
  });
});
