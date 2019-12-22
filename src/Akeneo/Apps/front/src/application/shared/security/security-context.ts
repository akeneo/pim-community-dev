import {createContext} from 'react';
import {Security} from './security.interface';

export const SecurityContext = createContext<Security>({isGranted: () => true});
