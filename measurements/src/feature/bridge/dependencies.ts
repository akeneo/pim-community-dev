import {dependencies} from '@akeneo-pim-community/legacy-bridge';

const measurementsDependencies = {
  router: dependencies.router,
  translate: dependencies.translate,
  viewBuilder: dependencies.viewBuilder,
  notify: dependencies.notify,
  user: dependencies.user,
  security: dependencies.security,
  //@ts-ignore
  config: __moduleConfig,
  unsavedChanges: {
    hasUnsavedChanges: false,
    setHasUnsavedChanges: (newValue: boolean) => {
      measurementsDependencies.unsavedChanges.hasUnsavedChanges = newValue;
    },
  },
};

export {measurementsDependencies};
