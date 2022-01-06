import React from 'react';
import {Locale as LocaleFlag, SelectInput} from 'akeneo-design-system';
import {useTranslate, LocaleCode, Locale, LocaleReference} from '@akeneo-pim-community/shared';

type LocaleDropdownProps = {
  title?: string;
  readOnly?: boolean;
  locale: LocaleReference;
  onChange: (newLocale: LocaleCode) => void;
  locales: Locale[];
};

const LocaleDropdown = ({locale, locales, ...rest}: LocaleDropdownProps) => {
  const translate = useTranslate();

  return (
    <SelectInput
      value={locale}
      clearable={false}
      placeholder={translate('pim_asset_manager.asset.mass_edit.select.locale')}
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
