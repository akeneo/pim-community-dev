import React from 'react';
import { Locale, LocaleCode } from "../models";
import {
  Dropdown,
  useBooleanState,
  Locale as LocaleWithFlag,
  AkeneoThemedProps,
  pimTheme,
  getColor
} from 'akeneo-design-system';
import styled, {css} from "styled-components";
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

const DropdownContainer = styled(Dropdown)`
  text-transform: none;
  font-size: ${pimTheme.fontSize.default};
  color: ${getColor('grey', 120)};
`

const HighlightLocaleWithFlag = styled(LocaleWithFlag)<{ highlighted?: boolean } & AkeneoThemedProps>`
  ${({highlighted}) => highlighted && css` color: ${getColor('purple100')};`}
`

const LocaleButton = styled.button`
  background: none;
  border: 0;
  line-height: 26px;
  color: ${getColor('grey', 120)};
  cursor: pointer;
`;

type LocaleSelectorProps = {
  value: LocaleCode;
  values: Locale[];
  onChange: (localeCode: LocaleCode) => void;
}

const LocaleSelector = ({
  value,
  values,
  onChange
}: LocaleSelectorProps) => {
  const translate = useTranslate();
  const [isOpen, open, close] = useBooleanState();
  const selectedLocale: Locale = values.find((locale) => locale.code === value) || values[0];

  return <DropdownContainer>
    {translate('pim_enrich.entity.locale.uppercase_label')}:
    <LocaleButton onClick={(e) => {
      e.stopPropagation();
      open();
    }}>
      <HighlightLocaleWithFlag code={selectedLocale.code} languageLabel={selectedLocale.label} highlighted={true}/>
    </LocaleButton>
    {isOpen &&
    <Dropdown.Overlay verticalPosition="down" onClose={close}>
      <Dropdown.Header>
        <Dropdown.Title>{translate('pim_enrich.entity.locale.uppercase_label')}</Dropdown.Title>
      </Dropdown.Header>
      <Dropdown.ItemCollection>
        {values.map((locale) =>
          <Dropdown.Item aria-selected={locale.code === value} key={locale.code} onClick={() => {
            close();
            onChange(locale.code);
          }}>
            <HighlightLocaleWithFlag code={locale.code} languageLabel={locale.label} highlighted={locale.code === value}/>
          </Dropdown.Item>
        )}
      </Dropdown.ItemCollection>
    </Dropdown.Overlay>
    }
  </DropdownContainer>
}

export { LocaleSelector };
