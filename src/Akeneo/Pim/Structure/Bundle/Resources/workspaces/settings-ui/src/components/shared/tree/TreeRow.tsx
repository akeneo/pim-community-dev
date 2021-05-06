import styled, {css} from 'styled-components';
import {AkeneoThemedProps, ArrowRightIcon, getColor} from 'akeneo-design-system';

const TreeRow = styled.div<{$selected: boolean; $disabled: boolean} & AkeneoThemedProps>`
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  flex-wrap: nowrap;

  box-sizing: border-box;
  height: 54px;
  line-height: 54px;
  padding: 0 20px;
  overflow: hidden;
  width: 100%;
  border: 1px solid ${getColor('grey100')};

  ${({$selected}) =>
    $selected &&
    css`
      color: ${getColor('blue100')};
      border: 1px solid ${getColor('blue100')};
    `}

  ${({$disabled}) =>
    $disabled &&
    css`
      border: 1px solid ${getColor('red100')};
    `}
`;

const ArrowButton = styled.button`
  height: 30px;
  width: 30px;
  vertical-align: middle;
  margin-right: 2px;
  padding: 0;
  border: none;
  background: none;

  &:not(:disabled) {
    cursor: pointer;
  }
`;

const TreeArrowIcon = styled(ArrowRightIcon)<{$isFolderOpen: boolean} & AkeneoThemedProps>`
  transform: rotate(${({$isFolderOpen}) => ($isFolderOpen ? '90' : '0')}deg);
  transition: transform 0.2s ease-out;
  vertical-align: middle;
  color: ${getColor('grey100')};
  cursor: pointer;
`;

const RowInnerContainer = styled.div`
  display: flex;
  flex-grow: 1;
  z-index: 1;
  max-width: 65%;
`;

const RowActionsContainer = styled.div`
  display: flex;
  opacity: 0;
  z-index: 1;

  ${TreeRow}:hover & {
    opacity: 1;
  }
`;

export {TreeRow, TreeArrowIcon, ArrowButton, RowActionsContainer, RowInnerContainer};
