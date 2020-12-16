import React, {useContext, ReactNode} from 'react';

type Translator = (id: string, placeholders?: {[name: string]: string}, count?: number) => string;

const defaultTranslator = (id: string) => id;

const TranslateContext = React.createContext(defaultTranslator);

const useTranslate = (): Translator => {
  const translator = useContext(TranslateContext);

  if (!translator) {
    throw new Error('[DependenciesContext]: Translate has not been properly initiated');
  }

  return translator;
};

const TranslateProvider = ({value, children}: {value: Translator; children: ReactNode}) => {
  return <TranslateContext.Provider value={value}>{children}</TranslateContext.Provider>;
};

export {useTranslate, TranslateContext, TranslateProvider};
