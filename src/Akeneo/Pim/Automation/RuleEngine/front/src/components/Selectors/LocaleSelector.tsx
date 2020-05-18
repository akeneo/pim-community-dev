import React from 'react';
import { option, optionsGroup, Select2Wrapper } from '../Select2Wrapper';
import { Locale } from '../../models';

type Props = {
  id: string;
  label: string;
  hiddenLabel?: boolean;
  currentLocaleCode: string;
  availableLocales: Locale[];
  onSelectorChange: (value: string) => void;
};

const LocaleSelector: React.FC<Props> = ({
  id,
  label,
  hiddenLabel = false,
  currentLocaleCode,
  availableLocales,
  onSelectorChange,
}) => {
  const [value, setValue] = React.useState<string>(currentLocaleCode);

  const localeChoices = availableLocales.map((locale: Locale) => {
    return {
      id: locale.code,
      text: locale.language,
    };
  });

  const localeIsFound = availableLocales.find(
    (locale: Locale) => locale.code === value
  );
  if (!localeIsFound) {
    setValue(availableLocales.length > 0 ? availableLocales[0].code : '');
  }

  const formatLocale = (item: option | optionsGroup): string => {
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
    <Select2Wrapper
      id={id}
      label={label}
      hiddenLabel={hiddenLabel}
      onChange={(value: string | string[] | number) => {
        setValue(value as string);
        onSelectorChange(value as string);
      }}
      value={value}
      data={localeChoices}
      hideSearch={true}
      formatResult={formatLocale}
      formatSelection={formatLocale}
    />
  );
};

export { LocaleSelector };
