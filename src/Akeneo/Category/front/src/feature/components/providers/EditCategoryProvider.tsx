import React, {createContext, FC, useEffect} from 'react';
import {fromPairs} from 'lodash/fp';
import {Channel, Locale, useFetch, useRoute} from '@akeneo-pim-community/shared';

type SetCanLeavePage = (canLeavePage: boolean) => void;

type Channels = {
  [code: string]: Channel;
};

type Locales = {
  [code: string]: Locale;
};

type EditCategoryContextContent = {
  setCanLeavePage: SetCanLeavePage;
  channels: Channels;
  channelsFetchFailed: boolean;
  locales: Locales;
  localesFetchFailed: boolean;
};

const EditCategoryContext = createContext<EditCategoryContextContent>({
  setCanLeavePage: () => {},
  channels: {},
  channelsFetchFailed: false,
  locales: {},
  localesFetchFailed: false,
});

type Props = {
  setCanLeavePage: SetCanLeavePage;
};

const EditCategoryProvider: FC<Props> = ({children, setCanLeavePage}) => {
  const channelsUrl = useRoute('pim_enrich_channel_rest_index');
  let [channelsArray, fetchChannels, statusFetchChannels] = useFetch<Channel[]>(channelsUrl);

  let channels: Channels = {};
  let channelsFetchFailed = statusFetchChannels === 'error';

  if (channelsArray !== null) {
    channels = fromPairs(channelsArray.map(channel => [channel.code, channel]));
  }

  const localesUrl = useRoute('pim_enrich_locale_rest_index', {activated: 'true'});
  let [localesArray, fetchLocales, statusFetchLocales] = useFetch<Locale[]>(localesUrl);

  let locales: Locales = {};
  let localesFetchFailed = statusFetchLocales === 'error';

  if (localesArray !== null) {
    locales = fromPairs(localesArray.map(locale => [locale.code, locale]));
  }

  useEffect(() => {
    fetchLocales();
    fetchChannels();
  }, [fetchLocales, fetchChannels]);

  return (
    <EditCategoryContext.Provider value={{setCanLeavePage, channels, channelsFetchFailed, locales, localesFetchFailed}}>
      {children}
    </EditCategoryContext.Provider>
  );
};

export {EditCategoryProvider, EditCategoryContext};
