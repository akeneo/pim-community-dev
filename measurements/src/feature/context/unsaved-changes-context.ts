import {createContext} from 'react';

type UnsavedChangesContextValue = {
  hasUnsavedChanges: boolean;
  setHasUnsavedChanges: (newValue: boolean) => void;
};

const UnsavedChangesContext = createContext<UnsavedChangesContextValue>({
  hasUnsavedChanges: false,
  setHasUnsavedChanges: () => {},
});

export {UnsavedChangesContextValue, UnsavedChangesContext};
