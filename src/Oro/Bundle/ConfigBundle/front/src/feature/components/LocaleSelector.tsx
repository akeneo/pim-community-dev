import {Locale, LocaleCode, useRoute, useTranslate} from '@akeneo-pim-community/shared';
import {Helper, SelectInput, Locale as LocaleComponent} from 'akeneo-design-system';
import {useFetchSimpler} from '../hooks/useFetchSimpler';
import React, {useEffect} from 'react';

interface Props {
  value: LocaleCode;
  onChange: (value: LocaleCode) => void;
}
export function LocaleSelector(props: Props) {
  const __ = useTranslate();
  const localeUrl = useRoute('pim_localization_locale_index');

  const [localesFetchResult, doFetchLocales] = useFetchSimpler<Locale[]>(localeUrl);

  useEffect(() => {
    doFetchLocales();
  }, [doFetchLocales]);

  const helperLoading = (
    <Helper inline level="info">
      Loading languages …
    </Helper>
  );

  switch (localesFetchResult.type) {
    case 'idle': // intentional no break;
    case 'fetching':
      return helperLoading;
    case 'error':
      return (
        <Helper inline level="error">
          {__('Unexpected error occurred. Please contact system administrator.')}: {localesFetchResult.message}
        </Helper>
      );
  }
  return (
    <SelectInput
      id="system-locale"
      name="system-locale"
      openLabel={__('pim_common.open')}
      emptyResultLabel={__('pim_common.no_result')}
      onChange={props.onChange}
      clearable={false}
      value={props.value}
    >
      {localesFetchResult.payload.map(locale => {
        return (
          <SelectInput.Option key={locale.code} title={locale.label} value={locale.code}>
            <LocaleComponent code={locale.code} languageLabel={`${locale.language} (${locale.region})`} />
          </SelectInput.Option>
        );
      })}
    </SelectInput>
  );
}
