import {ArrowDownIcon, Button, Dropdown, Locale as LocaleLabel, useBooleanState} from 'akeneo-design-system';
import Locale, {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import React from 'react';

type LocaleDropdownProps = {
  readOnly: boolean;
  locale: LocaleCode;
  onChange: (newLocale: LocaleCode) => void;
  locales: Locale[];
};

const LocaleDropdown = ({
  readOnly,
  locale,
  onChange,
  locales,
}: LocaleDropdownProps) => {
  const [isOpen, open, close] = useBooleanState();
  const currentLocale = locales.find(localeItem => localeItem.code === locale);

  if (undefined === currentLocale) {
    return null;
  }

  return (
    <Dropdown>
      <Button disabled={readOnly} onClick={open}>
        <LocaleLabel code={currentLocale.code} languageLabel={currentLocale.language} /> <ArrowDownIcon />
      </Button>
      {isOpen && (
        <Dropdown.Overlay verticalPosition="down" onClose={close}>
          <Dropdown.Header>
            <Dropdown.Title>Locales</Dropdown.Title>
          </Dropdown.Header>
          <Dropdown.ItemCollection>
            {locales.map(currentLocale => (
              <Dropdown.Item
                key={currentLocale.code}
                onClick={() => {
                  onChange(currentLocale.code);
                  close();
                }}
              >
                <LocaleLabel code={currentLocale.code} languageLabel={currentLocale.language} />
              </Dropdown.Item>
            ))}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

export {LocaleDropdown};
