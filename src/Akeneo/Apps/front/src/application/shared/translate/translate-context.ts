import * as React from 'react';
import {Translate} from './translate.interface';

export const TranslateContext = React.createContext<Translate>(id => id);
