/* eslint-disable @typescript-eslint/no-var-requires */
const router = require('pim/router');
const translate = require('oro/translator');
const viewBuilder = require('pim/form-builder');
const messenger = require('oro/messenger');
const userContext = require('pim/user-context');
const securityContext = require('pim/security-context');
const mediator = require('oro/mediator');

export const dependencies = {
  router,
  translate,
  viewBuilder,
  mediator,
  notify: messenger.notify.bind(messenger),
  user: userContext,
  security: {
    isGranted: securityContext.isGranted.bind(securityContext),
  },
};
