import React, {useEffect, useRef, useState} from 'react';
import {Search, useAutoFocus} from 'akeneo-design-system';
import {
  LocaleSelector,
  useDebounce,
  useTranslate,
  localeExists,
  LocaleCode,
  Channel,
  ChannelCode,
} from '@akeneo-pim-community/shared';
import {Context} from 'akeneoassetmanager/domain/model/context';
import {ChannelSwitcher} from 'akeneoassetmanager/application/component/app/channel-switcher';
import {useChannels} from 'akeneoassetmanager/application/hooks/channel';
import {
  CompletenessFilter,
  CompletenessValue,
} from 'akeneoassetmanager/application/component/asset/list/completeness-filter';
import {getLocales} from 'akeneoassetmanager/application/reducer/structure';

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
  const locales = getLocales(channels, context.channel);
  setLocaleIfNotExists(onContextChange, channels, context.channel, context.locale);
  const [userSearch, setUserSearch] = useState<string>(searchValue);
  const debouncedUserSearch = useDebounce(userSearch, 250);
  const inputRef = useRef<HTMLInputElement>(null);

  useAutoFocus(inputRef);

  useEffect(() => {
    onSearchChange(debouncedUserSearch);
  }, [debouncedUserSearch]);

  return (
    <Search
      searchValue={userSearch}
      onSearchChange={setUserSearch}
      placeholder={translate('pim_asset_manager.asset.grid.search')}
      inputRef={inputRef}
    >
      <Search.ResultCount>
        {translate('pim_asset_manager.result_counter', {count: resultCount ?? 0}, resultCount ?? 0)}
      </Search.ResultCount>
      <Search.Separator />
      {onCompletenessChange !== undefined && completenessValue !== undefined && (
        <CompletenessFilter value={completenessValue} onChange={onCompletenessChange} />
      )}
      {0 < channels.length && (
        <ChannelSwitcher
          channelCode={context.channel}
          channels={channels}
          locale={context.locale}
          onChannelChange={(channel: ChannelCode) => onContextChange({...context, channel})}
        />
      )}
      {0 < locales.length && (
        <LocaleSelector
          value={context.locale}
          values={getLocales(channels, context.channel)}
          onChange={locale => onContextChange({...context, locale})}
        />
      )}
    </Search>
  );
};

export {SearchBar};
