import * as React from 'react';
import Locale from 'akeneoassetmanager/domain/model/locale';
import Flag from 'akeneoassetmanager/tools/component/flag';
import Dropdown, {DropdownElement} from 'akeneoassetmanager/application/component/app/dropdown';
import {Key} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

const LocaleItemView = ({
  isOpen,
  element,
  isActive,
  onClick,
}: {
  isOpen: boolean;
  element: DropdownElement;
  isActive: boolean;
  onClick: (element: DropdownElement) => void;
}): JSX.Element => {
  const menuLinkClass = `AknDropdown-menuLink ${isActive ? `AknDropdown-menuLink--active` : ''}`;

  return (
    <div
      className={menuLinkClass}
      data-identifier={element.identifier}
      onClick={() => onClick(element)}
      tabIndex={isOpen ? 0 : -1}
      onKeyPress={event => {
        if (Key.Space === event.key) onClick(element);
      }}
    >
      <span className="label">
        <Flag locale={element.original} displayLanguage />
      </span>
    </div>
  );
};

const LocaleButtonView = ({selectedElement, onClick}: {selectedElement: DropdownElement; onClick: () => void}) => {
  const translate = useTranslate();

  return (
    <div
      className="AknActionButton AknActionButton--light AknActionButton--withoutBorder"
      data-identifier={selectedElement.identifier}
      onClick={onClick}
      tabIndex={0}
      onKeyPress={event => {
        if (Key.Space === event.key) onClick();
      }}
    >
      {translate('pim_common.locale')}
      :&nbsp;
      <span className="AknActionButton-highlight" data-identifier={selectedElement.identifier}>
        <Flag locale={selectedElement.original} displayLanguage />
      </span>
      <span className="AknActionButton-caret" />
    </div>
  );
};

const LocaleSwitcher = ({
  localeCode,
  locales,
  onLocaleChange,
  className = '',
}: {
  localeCode: string;
  locales: Locale[];
  onLocaleChange: (locale: Locale) => void;
  className?: string;
}) => {
  const translate = useTranslate();

  return (
    <Dropdown
      elements={locales.map((locale: Locale) => {
        return {
          identifier: locale.code,
          label: locale.label,
          original: locale,
        };
      })}
      label={translate('pim_common.locale')}
      selectedElement={localeCode}
      ItemView={LocaleItemView}
      ButtonView={LocaleButtonView}
      onSelectionChange={(locale: DropdownElement) => onLocaleChange(locale.original)}
      className={'locale-switcher ' + className}
    />
  );
};

export default LocaleSwitcher;
