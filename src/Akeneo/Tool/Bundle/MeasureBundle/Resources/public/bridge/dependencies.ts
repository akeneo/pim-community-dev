const measurementsDependencies = {
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
