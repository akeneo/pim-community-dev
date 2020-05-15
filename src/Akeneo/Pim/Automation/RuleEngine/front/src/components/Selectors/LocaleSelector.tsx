import React, { useEffect } from 'react';
import { useFormContext } from 'react-hook-form';
import { Locale } from '../../models';
import {
  Select2Option,
  Select2OptionGroup,
  Select2SimpleSyncWrapper,
} from '../Select2Wrapper';

type Props = {
  id: string;
  label: string;
  hiddenLabel?: boolean;
  availableLocales: Locale[];
  name: string;
};

const LocaleSelector: React.FC<Props> = ({
  id,
  label,
  hiddenLabel = false,
  availableLocales,
  name,
}) => {
  const { watch, setValue } = useFormContext();

  const getFormLocale = () => watch(name);

  const resetLocale = () => {
    setValue(name, undefined);
  };

  useEffect(() => {
    if (
      getFormLocale() &&
      !availableLocales.map(locale => locale.code).includes(getFormLocale())
    ) {
      resetLocale();
    }
  }, [getFormLocale(), availableLocales]);

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
    <Select2SimpleSyncWrapper
      id={id}
      label={label}
      hiddenLabel={hiddenLabel}
      data={localeChoices}
      hideSearch={true}
      formatResult={formatLocale}
      formatSelection={formatLocale}
      name={name}
      placeholder={'Locale'}
    />
  );
};

export { LocaleSelector };
