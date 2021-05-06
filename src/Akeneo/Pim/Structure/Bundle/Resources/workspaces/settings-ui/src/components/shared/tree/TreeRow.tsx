import styled, {css} from 'styled-components';
import {AkeneoThemedProps, ArrowRightIcon, getColor} from 'akeneo-design-system';

const treeRowBackgroundStyles = css<{$selected: boolean; $disabled: boolean; isRoot: boolean} & AkeneoThemedProps>`
  &:before {
    content: ' ';
    position: absolute;
    box-sizing: border-box;
    z-index: 0;
    left: 0;
    right: 0;
    padding: 0;
    width: 100%;
    height: 54px;

    background-color: ${getColor('white')};
    border-bottom: 1px solid ${getColor('grey60')};

    ${({isRoot}) =>
      isRoot &&
      css`
        border-top: 1px solid ${getColor('grey60')};
      `}

    ${({$selected}) =>
      $selected &&
      css`
        color: ${getColor('blue100')};
        border-color: ${getColor('blue100')};
      `}

    ${({$disabled}) =>
      $disabled &&
      css`
        border-color: ${getColor('red100')};
      `}
  }

  &:hover:before {
    background-color: ${getColor('grey20')};
  }
`;

const TreeRow = styled.div<{$selected: boolean; $disabled: boolean; isRoot: boolean} & AkeneoThemedProps>`
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

  ${treeRowBackgroundStyles}
`;

const ArrowButton = styled.button`
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

  &:hover {
    cursor: pointer;
  }
`;

const RowActionsContainer = styled.div`
  display: flex;
  opacity: 0;
  z-index: 1;

  ${TreeRow}:hover & {
    opacity: 1;
  }
`;

const DragInitiator = styled.div`
  color: ${getColor('grey100')};
  vertical-align: middle;
`;

export {TreeRow, TreeArrowIcon, ArrowButton, RowActionsContainer, RowInnerContainer, DragInitiator};
