import {__} from 'akeneomeasure/bridge/legacy/translator';

const router = require('pim/router');
const viewBuilder = require('pim/form-builder');
const userContext = require('pim/user-context');
const messenger = require('oro/messenger');

const dependencies = {
  router,
  translate: __,
  legacy: {
    viewBuilder,
  },
  user: userContext.get.bind(userContext),
  notify: messenger.notify.bind(messenger),
};

export {dependencies};
