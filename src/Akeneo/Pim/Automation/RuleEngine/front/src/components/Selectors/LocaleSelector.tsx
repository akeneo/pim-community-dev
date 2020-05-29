import React from 'react';
import { Locale, LocaleCode } from '../../models';
import {
  Select2Option,
  Select2OptionGroup,
  Select2SimpleSyncWrapper,
} from '../Select2Wrapper';
import { Translate } from '../../dependenciesTools';

type Props = {
  id: string;
  label: string;
  hiddenLabel?: boolean;
  availableLocales: Locale[];
  value: LocaleCode;
  onChange: (value: LocaleCode) => void;
  translate: Translate;
  allowClear?: boolean;
};

const LocaleSelector: React.FC<Props> = ({
  id,
  label,
  hiddenLabel = false,
  availableLocales,
  value,
  onChange,
  translate,
  children,
  allowClear = false,
}) => {
  const localeChoices = availableLocales.map((locale: Locale) => {
    return {
      id: locale.code,
      text: locale.language,
    };
  });

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

  return (
    <>
      <Select2SimpleSyncWrapper
        id={id}
        label={label}
        hiddenLabel={hiddenLabel}
        data={localeChoices}
        hideSearch={true}
        formatResult={formatLocale}
        formatSelection={formatLocale}
        placeholder={translate('pim_enrich.entity.locale.uppercase_label')}
        value={value}
        onValueChange={value => onChange(value as LocaleCode)}
        allowClear={allowClear}
      />
      {children}
    </>
  );
};

export { LocaleSelector };
