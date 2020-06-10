import React from 'react';
import { Locale, LocaleCode } from '../../models';
import {
  Select2Option,
  Select2OptionGroup,
  Select2SimpleSyncWrapper,
  Select2Value,
} from '../Select2Wrapper';
import { useTranslate } from '../../dependenciesTools/hooks';

type Props = {
  label?: string;
  hiddenLabel?: boolean;
  availableLocales: Locale[];
  value?: LocaleCode;
  onChange?: (value: LocaleCode) => void;
  allowClear?: boolean;
  disabled?: boolean;
  name: string;
  validation?: { required?: string; validate?: (value: any) => string | true };
};

const LocaleSelector: React.FC<Props> = ({
  label,
  hiddenLabel = false,
  availableLocales,
  value,
  onChange,
  children,
  allowClear = false,
  disabled = false,
  name,
  validation,
}) => {
  const translate = useTranslate();
  const localeChoices = availableLocales.map((locale: Locale) => {
    return {
      id: locale.code,
      text: locale.language,
    };
  });

  if (value && !localeChoices.some(localeChoice => localeChoice.id === value)) {
    localeChoices.push({
      id: value,
      text: `[${value}]`,
    });
  }

  const formatLocale = (item: Select2Option | Select2OptionGroup): string => {
    const locale = availableLocales.find(
      (locale: Locale) => locale.code === item.id
    );
    if (!locale) {
      return item.id as string;
    }
    const localeCode = locale.code;
    const shortRegion = localeCode.toLowerCase().split('_')[
      localeCode.split('_').length - 1
    ];

    return `<i class="flag flag-${shortRegion}"}/>&nbsp;${locale.language}`;
  };

  const handleChange = (value: Select2Value) => {
    if (onChange) {
      onChange(value as LocaleCode);
    }
  };

  return (
    <>
      <Select2SimpleSyncWrapper
        label={label || translate('pim_enrich.entity.locale.uppercase_label')}
        hiddenLabel={hiddenLabel}
        data={localeChoices}
        hideSearch={true}
        formatResult={formatLocale}
        formatSelection={formatLocale}
        placeholder={translate('pim_enrich.entity.locale.uppercase_label')}
        value={value || null}
        onChange={handleChange}
        allowClear={allowClear}
        disabled={disabled}
        name={name}
        validation={validation}
      />
      {children}
    </>
  );
};

export { LocaleSelector };
