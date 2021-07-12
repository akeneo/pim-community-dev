import React from 'react';
import {Locale, LocaleCode} from '@akeneo-pim-community/shared';

type LocaleSwitcherProps = {
  locales: Locale[];
  localeCode: LocaleCode;
  onChange: (localeCode: LocaleCode) => void;
};

const LocaleSwitcher: React.FC<LocaleSwitcherProps> = ({locales, onChange}) => {
  return (
    <>
      {locales.map(locale => (
        <button key={locale.code} onClick={() => onChange(locale.code)}>
          Fake LocaleSwitcher {locale.code}
        </button>
      ))}
    </>
  );
};

export {LocaleSwitcher};
