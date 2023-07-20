import styled, {css} from 'styled-components';
import {AkeneoThemedProps, ArrowRightIcon, getColor} from 'akeneo-design-system';
import {PlaceholderPosition} from '../../../models';

type TreeRowProps = {
  $selected: boolean;
  $disabled: boolean;
  isRoot: boolean;
  placeholderPosition?: PlaceholderPosition;
};

const placeholderPositionStyles = css<{placeholderPosition?: PlaceholderPosition} & AkeneoThemedProps>`
  &:after {
    content: ' ';
    position: absolute;
    box-sizing: border-box;
    z-index: 1;
    left: 0;
    right: 0;
    padding: 0;
    width: 100%;
    height: 4px;
    margin-top: -2px;
    background: linear-gradient(to top, ${getColor('blue40')} 4px, ${getColor('white')} 0px);
    pointer-events: none;

    ${({placeholderPosition}) =>
      placeholderPosition === 'bottom' &&
      css`
        margin-top: 52px;
      `}
  }
`;

const treeRowBackgroundStyles = css<TreeRowProps & AkeneoThemedProps>`
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
    transition: background-color 0.2s ease;

    ${({$disabled}) =>
      $disabled &&
      css`
        background-color: ${getColor('grey20')};
      `}

    ${({$selected}) =>
      $selected &&
      css`
        color: ${getColor('blue100')};
        background-color: ${getColor('blue20')};
      `}
  }

  &:hover:before {
    background-color: ${({$selected}) => getColor($selected ? 'blue20' : 'grey20')};
  }

  ${({placeholderPosition}) =>
    (placeholderPosition === 'bottom' || placeholderPosition === 'top') && placeholderPositionStyles}
`;

const TreeRow = styled.div<TreeRowProps & AkeneoThemedProps>`
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

  ${({$selected}) =>
    $selected &&
    css`
      color: ${getColor('blue100')};
    `}

  ${({$disabled}) =>
    $disabled &&
    css`
      opacity: 40%;
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

const TreeArrowIcon: any = styled(ArrowRightIcon)<{$isFolderOpen: boolean} & AkeneoThemedProps>`
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

  align-items: center;

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
  cursor: grab;
  display: flex;
  align-items: center;
  justify-content: center;
  color: ${getColor('grey100')};

  :active {
    cursor: grabbing;
  }
`;

export type {PlaceholderPosition};
export {TreeRow, TreeArrowIcon, ArrowButton, RowActionsContainer, RowInnerContainer, DragInitiator};
