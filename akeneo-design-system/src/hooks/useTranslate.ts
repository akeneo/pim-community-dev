import React, {useContext} from 'react';

export type Translate = (id: string, placeholders?: {[name: string]: string | number}, count?: number) => string;

const TranslateContext = React.createContext<Translate>(id => id);
const useTranslate = () => useContext<Translate>(TranslateContext);

export {useTranslate, TranslateContext};
