import * as React from 'react';
import {translate} from '../service/shared/translate.interface';

export const TranslateContext = React.createContext<translate>(id => id);
