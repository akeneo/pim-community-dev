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
    // eslint-disable-next-line @typescript-eslint/no-empty-function
    setHasUnsavedChanges: () => {},
  },
};

const IdentifierGeneratorContext = createContext<IdentifierGeneratorContextType>(defaultValue);

export {IdentifierGeneratorContext};
export type {IdentifierGeneratorContextType};
