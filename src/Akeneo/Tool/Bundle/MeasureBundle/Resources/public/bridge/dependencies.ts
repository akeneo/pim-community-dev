import {__} from 'akeneomeasure/bridge/legacy/translator';

const router = require('pim/router');
const viewBuilder = require('pim/form-builder');
const userContext = require('pim/user-context');
const messenger = require('oro/messenger');
const securityContext = require('pim/security-context');

const dependencies = {
  router,
  translate: __,
  legacy: {
    viewBuilder,
  },
  unsavedChanges: {
    hasUnsavedChanges: false,
    setHasUnsavedChanges: (newValue: boolean) => {
      dependencies.unsavedChanges.hasUnsavedChanges = newValue;
    },
  },
  user: userContext.get.bind(userContext),
  notify: messenger.notify.bind(messenger),
  security: securityContext.isGranted.bind(securityContext),
  // @ts-ignore
  config: __moduleConfig,
};

export {dependencies};
