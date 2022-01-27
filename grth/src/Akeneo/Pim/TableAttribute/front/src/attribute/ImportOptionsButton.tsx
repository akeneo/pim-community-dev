import React from 'react';
import {ArrowDownIcon, Button, Dropdown, getColor, useBooleanState} from 'akeneo-design-system';
import {useAttributeWithOptions} from './useAttributeWithOptions';
import styled from 'styled-components';
import {AttributeCode} from '../models';

type ImportOptionsButtonProps = {
  onClick: (attributeCode: AttributeCode) => void;
};

const DropdownItem = styled(Dropdown.Item)`
  flex-direction: column;
  gap: 0;
  height: auto;
  align-items: flex-start;
  line-height: initial;
  margin: 10px 20px;
`;

const AttributeLabel = styled.div`
  font-size: ${({theme}) => theme.fontSize.bigger};
  color: ${getColor('grey', 140)};
`;
const OptionsCount = styled.div`
  font-size: ${({theme}) => theme.fontSize.small};
`;

const ImportOptionsButton: React.FC<ImportOptionsButtonProps> = ({onClick}) => {
  const [isOpen, open, close] = useBooleanState();
  const attributes = useAttributeWithOptions(isOpen);

  const handleClick = (attributeCode: AttributeCode) => {
    close();
    onClick(attributeCode);
  };

  return (
    <Dropdown>
      <Button level='tertiary' ghost={true} onClick={open}>
        Import from existing attribute <ArrowDownIcon />
      </Button>
      {isOpen && (
        <Dropdown.Overlay onClose={close} dropdownOpenerVisible={true}>
          <Dropdown.ItemCollection>
            {attributes.map(attribute => {
              return (
                <DropdownItem onClick={() => handleClick(attribute.code)} key={attribute.code}>
                  <AttributeLabel>{attribute.label}</AttributeLabel>
                  <OptionsCount>{attribute.options_count} options</OptionsCount>
                </DropdownItem>
              );
            })}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

export {ImportOptionsButton};
