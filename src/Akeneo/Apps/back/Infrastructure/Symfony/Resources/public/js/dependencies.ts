const router = require('pim/router');
const translate = require('oro/translator');
const viewBuilder = require('pim/form-builder');
const messenger = require('oro/messenger');

export const dependencies = {
  router,
  translate,
  viewBuilder,
  notify: messenger.notify.bind(messenger),
};
