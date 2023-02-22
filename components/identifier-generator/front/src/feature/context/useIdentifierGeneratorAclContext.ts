import {useContext} from 'react';
import {IdentifierGeneratorAclContext, IdentifierGeneratorAclContextType} from './IdentifierGeneratorAclContext';

const useIdentifierGeneratorAclContext = (): IdentifierGeneratorAclContextType => {
  const result = useContext(IdentifierGeneratorAclContext);

  /* istanbul ignore if */
  if (typeof result === 'undefined') {
    throw new Error('You called useIdentifierGeneratorAclContext outside of a ContextProvider');
  }

  return result;
};

export {useIdentifierGeneratorAclContext};
