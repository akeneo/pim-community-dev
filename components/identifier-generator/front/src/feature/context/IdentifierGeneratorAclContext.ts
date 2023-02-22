import {createContext} from 'react';

type IdentifierGeneratorAclContextType = {
  isManageIdentifierGeneratorAclGranted: boolean;
};

const IdentifierGeneratorAclContext = createContext<IdentifierGeneratorAclContextType | undefined>(undefined);

export {IdentifierGeneratorAclContext};
export type {IdentifierGeneratorAclContextType};
