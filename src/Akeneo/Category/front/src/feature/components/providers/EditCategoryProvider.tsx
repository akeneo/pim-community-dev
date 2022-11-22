import React, {createContext, FC, useEffect} from 'react';
import {QueryClient, QueryClientProvider} from 'react-query';
import {fromPairs} from 'lodash/fp';
import {Channel, Locale, useFeatureFlags, useFetch, useRoute} from '@akeneo-pim-community/shared';

type SetCanLeavePage = (canLeavePage: boolean) => void;

type Locales = {
  [code: string]: Locale;
};

type Channels = {
  [code: string]: Channel
}

type EditCategoryContextContent = {
  setCanLeavePage: SetCanLeavePage;
  locales: Locales;
  channels: Channels;
  localesFetchFailed: boolean;
  channelsFetchFailed: boolean;
};

const EditCategoryContext = createContext<EditCategoryContextContent>({
  setCanLeavePage: () => {},
  locales: {},
  channels: {},
  localesFetchFailed: false,
  channelsFetchFailed: false,
});

type Props = {
  setCanLeavePage: SetCanLeavePage;
};

const EditCategoryProvider: FC<Props> = ({children, setCanLeavePage}) => {
  const queryClient = new QueryClient();
  const featureFlags = useFeatureFlags();

  const localesURL = useRoute('pim_enrich_locale_rest_index', {activated: 'true'});
  // TODO use controller in Category which use Channel's Service Api ????
  const channelsURL = useRoute('pim_enrich_channel_rest_index');

  let [localesArray, fetchLocales, localesStatus] = useFetch<Locale[]>(localesURL);

  let locales: Locales = {};
  let localesFetchFailed = localesStatus === 'error';

  if (localesArray !== null) {
    locales = fromPairs(localesArray.map(locale => [locale.code, locale]));
  }

  let [channelsArray, fetchChannels, channelsStatus] = useFetch<Channel[]>(channelsURL);
  let channels: Channels = {};

  let channelsFetchFailed = channelsStatus === 'error';

  if (channelsArray !== null) {
    //TODO check because it uses lodash lib and we want to get rid of it
    channels = fromPairs(channelsArray.map(channel => [channel.code, channel]));
  }

  useEffect(() => {
    if (!featureFlags.isEnabled('enriched_category')) return; // unused in legacy part
    fetchLocales();
  }, [featureFlags, fetchLocales]);

  useEffect(() => {
    if (!featureFlags.isEnabled('enriched_category')) return; // unused in legacy part
    fetchChannels();
  }, [featureFlags, fetchChannels]);

  return (
    <QueryClientProvider client={queryClient}>
      <EditCategoryContext.Provider value={{setCanLeavePage, locales, channels, localesFetchFailed, channelsFetchFailed}}>
        {children}
      </EditCategoryContext.Provider>
    </QueryClientProvider>
  );
};

export {EditCategoryProvider, EditCategoryContext};
