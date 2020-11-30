import React, {ReactNode, Ref, SyntheticEvent} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../../theme';
import {Checkbox} from '../..';
import {SelectableContext} from '../SelectableContext';

type TableRowProps = {
  /**
   * Content of the row
   */
  children?: ReactNode;

  /**
   * Function called when the user clicks on the row checkbox, required when table is selectable
   */
  onSelectToggle?: (isSelected: boolean) => void;

  /**
   * Define if the row is selected, required when table is selectable
   */
  isSelected?: boolean;

  /**
   * Function called when the user clicks on the row
   */
  onClick?: (event: SyntheticEvent) => void;
};

const RowContainer = styled.tr<{isSelected: boolean; isClickable: boolean} & AkeneoThemedProps>`
  ${({isSelected}) =>
    isSelected &&
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
  opacity: ${({isVisible}) => (isVisible ? 1 : 0)};
  cursor: auto;
`;

const TableRow = React.forwardRef<HTMLTableRowElement, TableRowProps>(
  ({isSelected, onSelectToggle, onClick, children, ...rest}: TableRowProps, forwardedRef: Ref<HTMLTableRowElement>) => {
    const {isSelectable, displayCheckbox} = React.useContext(SelectableContext);
    if (isSelectable && (undefined === isSelected || undefined === onSelectToggle)) {
      throw Error('A row in a selectable table should have the prop "isSelected" and "onSelectToggle"');
    }

    const handleCheckboxChange = (e: SyntheticEvent) => {
      e.stopPropagation();
      undefined !== onSelectToggle && onSelectToggle(!isSelected);
    };

    return (
      <RowContainer
        ref={forwardedRef}
        isClickable={undefined !== onClick}
        isSelected={!!isSelected}
        onClick={onClick}
        {...rest}
      >
        {(isSelectable || isSelected) && (
          <CheckboxContainer
            aria-hidden={!displayCheckbox && !isSelected}
            isVisible={displayCheckbox || !!isSelected}
            onClick={handleCheckboxChange}
          >
            <Checkbox
              checked={!!isSelected}
              onChange={(_value, e) => {
                handleCheckboxChange(e);
              }}
            />
          </CheckboxContainer>
        )}
        {children}
      </RowContainer>
    );
  }
);

export {TableRow};
