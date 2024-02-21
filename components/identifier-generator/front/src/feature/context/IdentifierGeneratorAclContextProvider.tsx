import React from 'react';

import {useSecurity} from '@akeneo-pim-community/shared';
import {IdentifierGeneratorAclContext, IdentifierGeneratorAclContextType} from './IdentifierGeneratorAclContext';

const IdentifierGeneratorAclContextProvider: React.FC = ({children}) => {
  const security = useSecurity();
  const isManageIdentifierGeneratorAclGranted = security.isGranted('pim_identifier_generator_manage');

  const value: IdentifierGeneratorAclContextType = {
    isManageIdentifierGeneratorAclGranted: isManageIdentifierGeneratorAclGranted,
  };

  return <IdentifierGeneratorAclContext.Provider value={value}>{children}</IdentifierGeneratorAclContext.Provider>;
};

export {IdentifierGeneratorAclContextProvider};
