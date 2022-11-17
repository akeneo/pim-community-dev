import {useContext} from 'react';
import {IdentifierGeneratorContext, IdentifierGeneratorContextType} from './IdentifierGeneratorContext';

const useIdentifierGeneratorContext = (): IdentifierGeneratorContextType => useContext(IdentifierGeneratorContext);

export {useIdentifierGeneratorContext};
