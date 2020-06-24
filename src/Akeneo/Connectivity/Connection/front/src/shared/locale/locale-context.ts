import {createContext} from 'react';
import {Locale} from './use-locale';

export const LocaleContext = createContext<Locale[] | undefined>(undefined);
