import * as React from 'react';
import {translate} from './translate.interface';

export const TranslateContext = React.createContext<translate>(id => id);
