import {
  Analytics,
  DependenciesContextProps,
  featureFlags,
  mediator,
  securityContext,
  Router,
  translate,
  userContext,
  ViewBuilder,
} from '@akeneo-pim-community/shared';

/* eslint-disable @typescript-eslint/no-var-requires */

/**
 * The remaining legacy services are resolved when they are used, not when this module is loaded: some of them
 * (oro/messenger for instance) import the legacy bridge themselves, and an eager require would capture their
 * unfinished exports depending on the module loading order.
 */
const lazyLegacyModule = <T extends object>(load: () => any): T =>
  new Proxy({} as T, {
    get: (_target, property) => {
      const legacyModule = load();
      const value = legacyModule[property];

      return 'function' === typeof value ? value.bind(legacyModule) : value;
    },
  });

const messenger = lazyLegacyModule<{notify: DependenciesContextProps['notify']}>(() => require('oro/messenger'));

const dependencies: DependenciesContextProps = {
  router: lazyLegacyModule<Router>(() => require('pim/router')),
  translate,
  viewBuilder: lazyLegacyModule<ViewBuilder>(() => require('pim/form-builder')),
  notify: (...args: any[]) => (messenger.notify as any)(...args),
  user: userContext,
  security: {
    isGranted: securityContext.isGranted,
  },
  mediator,
  featureFlags,
  analytics: lazyLegacyModule<Analytics>(() => require('pim/analytics')),
};

export {dependencies};
