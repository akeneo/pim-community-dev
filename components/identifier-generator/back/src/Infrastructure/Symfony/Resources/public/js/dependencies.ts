const identifierGeneratorDependencies = {
    unsavedChanges: {
        hasUnsavedChanges: false,
        setHasUnsavedChanges: (newValue: boolean) => {
            identifierGeneratorDependencies.unsavedChanges.hasUnsavedChanges = newValue;
        },
    },
    isManageIdentifierGeneratorAclGranted: true
};

export {identifierGeneratorDependencies};
