import React from 'react';
import { Locale, LocaleCode } from "../models";
import {
  Dropdown,
  useBooleanState,
  Locale as LocaleWithFlag,
  AkeneoThemedProps,
  pimTheme,
  getColor,
  SwitcherButton
} from 'akeneo-design-system';
import styled, {css} from "styled-components";
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

const DropdownContainer = styled(Dropdown)`
  text-transform: none;
  font-size: ${pimTheme.fontSize.default};
  color: ${getColor('grey', 120)};
`

const HighlightLocaleWithFlag = styled(LocaleWithFlag)<{ selected?: boolean } & AkeneoThemedProps>`
  ${({selected}) => selected && css`
    color: ${getColor('purple100')};
    font-style: italic;
    font-weight: bold;
  `}
`

const LocaleDropdownItem = styled(Dropdown.Item)`
  justify-content: space-between;
`

type LocaleSelectorProps = {
  value: LocaleCode;
  values: Locale[];
  completeValues?: LocaleCode[];
  onChange?: (localeCode: LocaleCode) => void;
}

const LocaleSelector = ({
  value,
  values,
  completeValues,
  onChange
}: LocaleSelectorProps) => {
  const translate = useTranslate();
  const [isOpen, open, close] = useBooleanState();
  const selectedLocale: Locale = values.find((locale) => locale.code === value) || values[0];

  const handleChange = (localeCode: LocaleCode) => onChange?.(localeCode);

  return <DropdownContainer>
    <SwitcherButton
      label={translate('pim_enrich.entity.locale.plural_label')}
      onClick={open}
    >
      <HighlightLocaleWithFlag code={selectedLocale.code} languageLabel={selectedLocale.label}/>
    </SwitcherButton>
    {isOpen &&
    <Dropdown.Overlay verticalPosition="down" onClose={close}>
      <Dropdown.Header>
        <Dropdown.Title>{translate('pim_enrich.entity.attribute.module.edit.select_locale')}</Dropdown.Title>
      </Dropdown.Header>
      <Dropdown.ItemCollection>
        {values.map((locale) =>
          <LocaleDropdownItem aria-selected={locale.code === value} key={locale.code} onClick={() => {
            close();
            handleChange(locale.code);
          }}>
            <HighlightLocaleWithFlag code={locale.code} languageLabel={locale.label} selected={locale.code === value}/>
            { completeValues && !completeValues.includes(locale.code) &&
              <span className='AknBadge AknBadge--small AknBadge--highlight' data-testid={`LocaleSelector.incomplete.${locale.code}`}/>
            }
          </LocaleDropdownItem>
        )}
      </Dropdown.ItemCollection>
    </Dropdown.Overlay>
    }
  </DropdownContainer>
}

export { LocaleSelector };
