import React, {ReactNode, SyntheticEvent} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../../theme';
import {Checkbox} from '../..';
import {useSelectableContext} from '../SelectableContext';

type TableRowProps = {
  /**
   * Content of the row
   */
  children?: ReactNode;

  /**
   * Function called when the user clicks on the row checkbox
   */
  onSelectToggle?: (isSelected: boolean) => void;

  /**
   * Define if the row is selected
   */
  isSelected?: boolean;

  /**
   * Function called when the user clicks on the row
   */
  onClick?: (event: SyntheticEvent) => void;
};

const RowContainer = styled.tr<{isSelected: boolean; isClickable: boolean} & AkeneoThemedProps>`
  ${props =>
    props.isSelected &&
    css`
      > td {
        background-color: ${getColor('blue', 20)};
      }
    `};

  ${({isClickable}) =>
    isClickable &&
    css`
      &:hover {
        cursor: pointer;
      }
    `}

  &:hover > td {
    opacity: 1;
    ${({isClickable}) =>
      isClickable &&
      css`
        background-color: ${getColor('grey', 20)};
      `}
  }

  &:hover > td > div {
    opacity: 1;
  }
`;

const CheckboxContainer = styled.td<{isVisible: boolean}>`
  background: none !important;
  opacity: ${props => (props.isVisible ? 1 : 0)};
  cursor: auto;
`;

const TableRow = ({isSelected, onSelectToggle, onClick, children, ...rest}: TableRowProps) => {
  const {isSelectable, amountSelectedRows} = useSelectableContext();

  if (isSelectable && undefined === isSelected) {
    throw Error('A row in a selectable table should have the prop "isSelected"');
  }
  if (isSelectable && undefined === onSelectToggle) {
    throw Error('A row in a selectable table should have the prop "onSelectToggle"');
  }

  const isCheckboxVisible = undefined !== amountSelectedRows && amountSelectedRows > 0;

  const handleCheckboxChange = (e: SyntheticEvent) => {
    e.stopPropagation();
    undefined !== onSelectToggle && onSelectToggle(!isSelected);
  };

  return (
    <RowContainer isClickable={undefined !== onClick} isSelected={isSelected} onClick={onClick} {...rest}>
      {isSelectable && undefined !== isSelected && (
        <CheckboxContainer isVisible={isCheckboxVisible} onClick={handleCheckboxChange}>
          <Checkbox
            checked={isSelected}
            onChange={(_value, e) => {
              handleCheckboxChange(e);
            }}
          />
        </CheckboxContainer>
      )}
      {children}
    </RowContainer>
  );
};

export {TableRow};
