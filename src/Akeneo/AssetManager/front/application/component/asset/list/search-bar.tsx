import React from 'react';
import styled from 'styled-components';
import {getColor} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {Context} from 'akeneoassetmanager/domain/model/context';
import Locale, {localeExists, LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import Channel, {ChannelCode} from 'akeneoassetmanager/domain/model/channel';
import SearchField from 'akeneoassetmanager/application/component/asset/list/search-bar/search-field';
import LocaleSwitcher from 'akeneoassetmanager/application/component/app/locale-switcher';
import ChannelSwitcher from 'akeneoassetmanager/application/component/app/channel-switcher';
import {getLocales} from 'akeneoassetmanager/application/reducer/structure';
import {useChannels} from 'akeneoassetmanager/application/hooks/channel';
import {
  CompletenessFilter,
  CompletenessValue,
} from 'akeneoassetmanager/application/component/asset/list/completeness-filter';

type SearchProps = {
  dataProvider: any;
  searchValue: string;
  onSearchChange: (searchValue: string) => void;
  context: Context;
  onContextChange: (context: Context) => void;
  resultCount: number | null;
  completenessValue?: CompletenessValue;
  onCompletenessChange?: (value: CompletenessValue) => void;
};

const Container = styled.div`
  display: flex;
  padding: 10px 0;
  border-bottom: 1px solid ${getColor('grey', 100)};
  align-items: center;
`;

const Separator = styled.div`
  border-left: 1px solid ${getColor('grey', 100)};
  margin: 0 10px;
  margin-right: 5px;
  height: 20px;

  &:first-child,
  &:last-child {
    margin: 0;
  }
`;

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

const ResultCounter = styled.div`
  white-space: nowrap;
  color: ${getColor('brand', 100)};
  margin-left: 10px;
  line-height: 16px;
  text-transform: none;
`;

//TODO Use DSM Search
const SearchBar = ({
  searchValue,
  onSearchChange,
  resultCount,
  context,
  completenessValue,
  dataProvider,
  onContextChange,
  onCompletenessChange,
}: SearchProps) => {
  const translate = useTranslate();
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
      <ResultCounter>
        {translate('pim_asset_manager.result_counter', {count: resultCount ?? 0}, resultCount ?? 0)}
      </ResultCounter>
      <Separator />
      {onCompletenessChange !== undefined && completenessValue !== undefined && (
        <CompletenessFilter value={completenessValue} onChange={onCompletenessChange} />
      )}
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

export {SearchBar};
