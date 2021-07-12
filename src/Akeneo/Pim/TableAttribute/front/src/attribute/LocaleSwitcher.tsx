import React from 'react';
import {Locale, LocaleCode, useTranslate} from "@akeneo-pim-community/shared";
import {Dropdown, Locale as LocaleFlag, SwitcherButton, useBooleanState} from "akeneo-design-system";

type LocaleSwitcherProps = {
  locales: Locale[];
  localeCode: LocaleCode;
  onChange: (localeCode: LocaleCode) => void;
};

const LocaleSwitcher: React.FC<LocaleSwitcherProps> = ({
  locales,
  localeCode,
  onChange
}) => {
  const translate = useTranslate();
  const [isOpen, open, close] = useBooleanState();

  const handleChange = (localeCode: LocaleCode) => {
    onChange(localeCode);
    close();
  }

  const currentLocale = locales.find(locale => locale.code === localeCode);
  if (!currentLocale) return null;

  return <Dropdown>
    <SwitcherButton
      label={translate('pim_enrich.entity.locale.uppercase_label')}
      onClick={open}
    ><LocaleFlag
      code={currentLocale.code}
      languageLabel={currentLocale.language}
    /></SwitcherButton>
    {isOpen && <Dropdown.Overlay verticalPosition="down" onClose={close}>
      <Dropdown.Header>
        <Dropdown.Title>{translate('pim_enrich.entity.locale.uppercase_label')}</Dropdown.Title>
      </Dropdown.Header>
      <Dropdown.ItemCollection>
        {locales.map(locale => <Dropdown.Item key={locale.code} onClick={() => handleChange(locale.code)}>
          <LocaleFlag code={locale.code} languageLabel={locale.language}/>
        </Dropdown.Item>)}
      </Dropdown.ItemCollection>
    </Dropdown.Overlay>}
  </Dropdown>
}

export {LocaleSwitcher}
