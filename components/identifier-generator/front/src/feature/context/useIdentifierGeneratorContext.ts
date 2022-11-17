import {useContext} from 'react';
import {IdentifierGeneratorContext, IdentifierGeneratorContextType} from './IdentifierGeneratorContext';

const useIdentifierGeneratorContext = (): IdentifierGeneratorContextType => {
  const result = useContext(IdentifierGeneratorContext);

  if (typeof result === 'undefined') {
    throw new Error('You called useIdentifierGeneratorContext outside of a ContextProvider');
  }

  return result;
};

export {useIdentifierGeneratorContext};
