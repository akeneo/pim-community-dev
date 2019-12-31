import * as React from 'react';
import {Context} from 'akeneoassetmanager/domain/model/context';
import styled from 'styled-components';
import __ from 'akeneoassetmanager/tools/translator';
import Locale, {localeExists, LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import Channel, {ChannelCode} from 'akeneoassetmanager/domain/model/channel';
import SearchField from 'akeneoassetmanager/application/component/asset/list/search-bar/search-field';
import {ResultCounter} from 'akeneoassetmanager/application/component/app/result-counter';
import {Separator} from 'akeneoassetmanager/application/component/app/separator';
import LocaleSwitcher from 'akeneoassetmanager/application/component/app/locale-switcher';
import ChannelSwitcher from 'akeneoassetmanager/application/component/app/channel-switcher';
import {getLocales} from 'akeneoassetmanager/application/reducer/structure';

type SearchProps = {
  dataProvider: any;
  searchValue: string;
  onSearchChange: (searchValue: string) => void;
  context: Context;
  onContextChange: (context: Context) => void;
  resultCount: number | null;
};

const Container = styled.div`
  display: flex;
  padding: 10px 0;
  border-bottom: 1px solid ${props => props.theme.color.grey100};
  align-items: center;
`;

const AdjustedSeparator = styled(Separator)`
  margin-right: 5px;
`;

export const useChannels = (channelFetcher: any) => {
  const [channels, setChannels] = React.useState<Channel[]>([]);

  React.useEffect(() => {
    channelFetcher.fetchAll().then((channels: Channel[]) => {
      setChannels(channels);
    });
  }, []);

  return channels;
};

const setLocaleIfNotExists = (
  onContextChange: (context: Context) => void,
  channels: Channel[],
  channel: ChannelCode,
  locale: LocaleCode
) => {
  const locales = getLocales(channels, channel);
  if (channels.length !== 0 && locales.length !== 0 && !localeExists(locales, locale)) {
    onContextChange({channel, locale: locales[0].code});
  }
};

const SearchBar = ({searchValue, onSearchChange, resultCount, context, onContextChange, dataProvider}: SearchProps) => {
  const channels = useChannels(dataProvider.channelFetcher);
  setLocaleIfNotExists(onContextChange, channels, context.channel, context.locale);

  return (
    <Container data-container="search-bar">
      <SearchField
        value={searchValue}
        onChange={(newSearchValue: string) => {
          onSearchChange(newSearchValue);
        }}
      />
      <ResultCounter count={resultCount} />
      <AdjustedSeparator />
      <ChannelSwitcher
        channelCode={context.channel}
        channels={channels}
        locale={context.locale}
        onChannelChange={({code}: Channel) => {
          onContextChange({...context, channel: code});
        }}
      />
      <LocaleSwitcher
        localeCode={context.locale}
        locales={getLocales(channels, context.channel)}
        onLocaleChange={({code}: Locale) => {
          onContextChange({...context, locale: code});
        }}
      />
    </Container>
  );
};

export default SearchBar;
