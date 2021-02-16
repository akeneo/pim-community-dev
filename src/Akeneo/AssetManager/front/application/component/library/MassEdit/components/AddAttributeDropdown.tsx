import React from 'react';
import {AkeneoThemedProps, ArrowDownIcon, Button, Dropdown, getColor, useBooleanState} from 'akeneo-design-system';
import {getLabel} from 'pimui/js/i18n';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import styled, {css} from 'styled-components';

type AddAttributeDropdownProps = {
  attributes: NormalizedAttribute[];
  uiLocale: LocaleCode;
  alreadyUsed: string[];
  onAdd: (attribute: NormalizedAttribute) => void;
};

const AttributeItem = styled(Dropdown.Item)<{isAlreadyUsed: boolean} & AkeneoThemedProps>`
  ${({isAlreadyUsed}) => isAlreadyUsed && css`
    color: ${getColor('brand', 100)};
    font-style: italic;
    font-weight: 700;
  `}
`;

const AddAttributeDropdown = ({attributes, uiLocale, alreadyUsed, onAdd}: AddAttributeDropdownProps) => {
  const [isOpen, open, close] = useBooleanState(false);
  const translate = useTranslate();

  return (
    <Dropdown>
      <Button size="small" level="tertiary" ghost={true} onClick={open}>
        {translate('Add attributes')} <ArrowDownIcon />
      </Button>
      {isOpen && (
        <Dropdown.Overlay verticalPosition="down" onClose={close}>
          <Dropdown.Header>
            <Dropdown.Title>{translate('Attributes')}</Dropdown.Title>
          </Dropdown.Header>
          <Dropdown.ItemCollection>
            {attributes
              .filter(attribute => !attribute.is_read_only && attribute.type === 'text')
              .map(attribute => (
                <AttributeItem
                  key={attribute.identifier}
                  isAlreadyUsed={alreadyUsed.includes(attribute.identifier)}
                  onClick={() => {
                    onAdd(attribute);
                    close();
                  }}
                >
                  {getLabel(attribute.labels, uiLocale, attribute.code)}
                </AttributeItem>
              ))
            }
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

export {AddAttributeDropdown};
