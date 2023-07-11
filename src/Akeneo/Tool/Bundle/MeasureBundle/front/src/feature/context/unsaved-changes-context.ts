import {createContext} from 'react';

type UnsavedChangesContextValue = {
  hasUnsavedChanges: boolean;
  setHasUnsavedChanges: (newValue: boolean) => void;
};

const UnsavedChangesContext = createContext<UnsavedChangesContextValue>({
  hasUnsavedChanges: false,
  setHasUnsavedChanges: (_newValue: boolean) => {},
});

export {UnsavedChangesContext};
export type {UnsavedChangesContextValue};
