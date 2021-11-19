import React from 'react';
import {Locale as LocaleFlag, SelectInput} from 'akeneo-design-system';
import {useTranslate, LocaleCode, Locale} from '@akeneo-pim-community/shared';

type LocaleDropdownProps = {
  title?: string;
  readOnly?: boolean;
  locale: LocaleCode;
  onChange: (newLocale: LocaleCode) => void;
  locales: Locale[];
};

const LocaleDropdown = ({locale, locales, ...rest}: LocaleDropdownProps) => {
  const translate = useTranslate();
  const currentLocale = locales.find(localeItem => localeItem.code === locale);

  if (undefined === currentLocale) {
    return null;
  }

  return (
    <SelectInput
      value={currentLocale.code}
      clearable={false}
      emptyResultLabel={translate('pim_asset_manager.result_counter', {count: 0}, 0)}
      openLabel={translate('pim_common.open')}
      {...rest}
    >
      {locales.map(localeItem => (
        <SelectInput.Option key={localeItem.code} value={localeItem.code} title={localeItem.label}>
          <LocaleFlag code={localeItem.code} languageLabel={localeItem.language} />
        </SelectInput.Option>
      ))}
    </SelectInput>
  );
};

export {LocaleDropdown};
