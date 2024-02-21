import {useContext} from 'react';
import {SecurityContext} from './security-context';

export const useSecurity = () => useContext(SecurityContext);
