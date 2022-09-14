import React, {createContext, FC, useEffect} from 'react';
import {QueryClient, QueryClientProvider} from 'react-query';
import {fromPairs} from 'lodash/fp';
import {Locale, useFeatureFlags, useFetch, useRoute} from '@akeneo-pim-community/shared';

type SetCanLeavePage = (canLeavePage: boolean) => void;

type Locales = {
  [code: string]: Locale;
};

type EditCategoryContextContent = {
  setCanLeavePage: SetCanLeavePage;
  locales: Locales;
  localesFetchFailed: boolean;
};

const EditCategoryContext = createContext<EditCategoryContextContent>({
  setCanLeavePage: () => {},
  locales: {},
  localesFetchFailed: false,
});

type Props = {
  setCanLeavePage: SetCanLeavePage;
};

const EditCategoryProvider: FC<Props> = ({children, setCanLeavePage}) => {
  const queryClient = new QueryClient();
  const featureFlags = useFeatureFlags();

  const localesURL = useRoute('pim_enrich_locale_rest_index', {activated: 'true'});

  let [localesArray, fetchLocales, status] = useFetch<Locale[]>(localesURL);

  let locales: Locales = {};
  let localesFetchFailed = status === 'error';

  if (localesArray !== null) {
    locales = fromPairs(localesArray.map(locale => [locale.code, locale]));
  }

  useEffect(() => {
    if (!featureFlags.isEnabled('enriched_category')) return; // unused in legacy part
    fetchLocales();
  }, [featureFlags, fetchLocales]);

  return (
    <QueryClientProvider client={queryClient}>
      <EditCategoryContext.Provider value={{setCanLeavePage, locales, localesFetchFailed}}>
        {children}
      </EditCategoryContext.Provider>
    </QueryClientProvider>
  );
};

export {EditCategoryProvider, EditCategoryContext};
