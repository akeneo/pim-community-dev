import {dependencies} from '@akeneo-pim-community/legacy-bridge';

const measurementsDependencies = {
  ...dependencies,
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
