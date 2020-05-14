import React from 'react';
import { Locale } from '../../models';
import { Select2Option, Select2OptionGroup, Select2SimpleSyncWrapper } from "../Select2Wrapper";

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
    <Select2SimpleSyncWrapper
      id={id}
      label={label}
      hiddenLabel={hiddenLabel}
      onChange={(value: string) => {
        setValue(value);
        onSelectorChange(value);
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
