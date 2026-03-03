import {createContext} from 'react';

type IdentifierGeneratorContextType = {
  unsavedChanges: {
    hasUnsavedChanges: boolean;
    setHasUnsavedChanges: (newValue: boolean) => void;
  };
};

const IdentifierGeneratorContext = createContext<IdentifierGeneratorContextType | undefined>(undefined);

export {IdentifierGeneratorContext};
export type {IdentifierGeneratorContextType};
