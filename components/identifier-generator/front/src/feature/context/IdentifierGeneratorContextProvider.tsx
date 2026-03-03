import React from 'react';

import {IdentifierGeneratorContext, IdentifierGeneratorContextType} from './IdentifierGeneratorContext';

const IdentifierGeneratorContextProvider: React.FC = ({children}) => {
  const [hasUnsavedChanges, setHasUnsavedChanges] = React.useState<boolean>(false);

  const value: IdentifierGeneratorContextType = {
    unsavedChanges: {
      hasUnsavedChanges,
      setHasUnsavedChanges,
    },
  };

  return <IdentifierGeneratorContext.Provider value={value}>{children}</IdentifierGeneratorContext.Provider>;
};

export {IdentifierGeneratorContextProvider};
