import React, {useEffect, useRef, useState} from 'react';
import {Search, useAutoFocus} from 'akeneo-design-system';
import {useDebounce, useTranslate} from '@akeneo-pim-community/shared';
import {Context} from 'akeneoassetmanager/domain/model/context';
import Locale, {localeExists, LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import Channel, {ChannelCode} from 'akeneoassetmanager/domain/model/channel';
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
  setLocaleIfNotExists(onContextChange, channels, context.channel, context.locale);
  const [userSearch, setUserSearch] = useState<string>(searchValue);
  const debouncedUserSearch = useDebounce(userSearch, 250);
  const inputRef = useRef<HTMLInputElement>(null);

  useAutoFocus(inputRef);

  useEffect(() => {
    onSearchChange(debouncedUserSearch);
  }, [debouncedUserSearch]);

  return (
    <div>
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
      </Search>
    </div>
  );
};

export {SearchBar};
