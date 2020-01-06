import {createContext} from 'react';
import {Translate} from './translate.interface';

export const TranslateContext = createContext<Translate>(id => id);
