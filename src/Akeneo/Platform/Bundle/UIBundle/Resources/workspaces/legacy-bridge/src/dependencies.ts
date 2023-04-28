import {DependenciesContextProps, systemConfiguration} from '@akeneo-pim-community/shared';

/* eslint-disable @typescript-eslint/no-var-requires */
const router = require('pim/router');
const translate = require('oro/translator');
const viewBuilder = require('pim/form-builder');
const messenger = require('oro/messenger');
const userContext = require('pim/user-context');
const securityContext = require('pim/security-context');
const mediator = require('oro/mediator');
const featureFlags = require('pim/feature-flags');
const analytics = require('pim/analytics');

const dependencies: DependenciesContextProps = {
  router,
  translate,
  viewBuilder,
  notify: messenger.notify,
  user: userContext,
  security: {
    isGranted: securityContext.isGranted.bind(securityContext),
  },
  mediator,
  featureFlags,
  analytics,
  systemConfiguration,
};

export {dependencies};
