const router = require('pim/router');
const translate = require('oro/translator');
const viewBuilder = require('pim/form-builder');
const messenger = require('oro/messenger');
const userContext = require('pim/user-context');
const securityContext = require('pim/security-context');
const featureFlags = require('pim/feature-flags');
const permissionFormRegistry = require('pim/permission-form-registry').default;

export const dependencies = {
  router,
  translate,
  viewBuilder,
  notify: messenger.notify,
  user: userContext,
  security: {
    isGranted: securityContext.isGranted.bind(securityContext),
  },
  featureFlags,
  permissionFormRegistry: permissionFormRegistry,
};
