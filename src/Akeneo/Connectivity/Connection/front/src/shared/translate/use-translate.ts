import {useContext} from 'react';
import {TranslateContext} from './translate-context';

export const useTranslate = () => useContext(TranslateContext);
