import {createContext} from 'react';

type IdentifierGeneratorContextType = {
  unsavedChanges: {
    hasUnsavedChanges: boolean;
    setHasUnsavedChanges: (newValue: boolean) => void;
  };
};

const defaultValue = {
  unsavedChanges: {
    hasUnsavedChanges: false,
    setHasUnsavedChanges: () => null,
  },
};

const IdentifierGeneratorContext = createContext<IdentifierGeneratorContextType>(defaultValue);

export {IdentifierGeneratorContext};
export type {IdentifierGeneratorContextType};
