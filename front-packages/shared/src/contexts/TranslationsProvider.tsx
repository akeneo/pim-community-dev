import React, {createContext, FC, useState, useEffect, useContext, useRef} from 'react';
import {initTranslator} from '../dependencies/init-translator';
import {translate} from '../dependencies/translate';

type Translate = (id: string, placeholders?: {[name: string]: string}, count?: number) => string;

const placeholder: Translate = () => '';

export const TranslationsContext = createContext<Translate>(placeholder);

export const TranslationsProvider: FC = ({children}) => {
  const state = useRef<{isLoaded: boolean}>({isLoaded: false});
  const [implem, setImplem] = useState<Translate>(() => placeholder);

  useEffect(() => {
    const {isLoaded} = state.current;

    if (isLoaded) {
      return;
    }

    (async () => {
      await initTranslator.fetch();
      setImplem(() => translate);
      state.current.isLoaded = true;
    })();
  });

  return (
    <TranslationsContext.Provider value={implem}>
      {children}
    </TranslationsContext.Provider>
  );
};

export const useLegacyTranslate = (): Translate => {
  const translate = useContext<Translate>(TranslationsContext);

  if (!translate) {
    throw new Error('[DependenciesContext]: Translate has not been properly initiated');
  }

  return translate;
};
