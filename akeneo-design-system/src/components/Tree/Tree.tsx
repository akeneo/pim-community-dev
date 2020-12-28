import React, {Ref, SyntheticEvent, isValidElement, ReactElement, ReactNode} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, CommonStyle, getColor} from '../../theme';
import {Checkbox, CheckboxChecked} from '../Checkbox/Checkbox';
import {ArrowRightIcon, FolderIcon, FolderPlainIcon, FoldersIcon, FoldersPlainIcon, LoaderIcon} from '../../icons';

const folderIconCss = css`
  vertical-align: middle;
  transition: color 0.2s ease;
  margin-right: 5px;
`;

const TreeContainer = styled.li`
  display: block;
  color: ${getColor('grey140')};
`;

const SubTreesContainer = styled.ul`
  margin: 0 0 0 20px;
  padding: 0;
`;

const TreeArrowIcon = styled(ArrowRightIcon)<{$isFolderOpen: boolean} & AkeneoThemedProps>`
  transform: rotate(${({$isFolderOpen}) => ($isFolderOpen ? '90' : '0')}deg);
  transition: transform 0.2s ease-out;
  vertical-align: middle;
  color: ${getColor('grey100')};
  cursor: pointer;
`;

const TreeFolderIcon = styled(FolderIcon)`
  ${folderIconCss}
`;

const TreeFoldersPlainIcon = styled(FoldersPlainIcon)`
  ${folderIconCss}
  color: ${getColor('blue100')};
`;

const TreeFolderPlainIcon = styled(FolderPlainIcon)`
  ${folderIconCss}
  color: ${getColor('blue100')};
`;

const TreeFoldersIcon = styled(FoldersIcon)`
  ${folderIconCss}
`;

const TreeLoaderIcon = styled(LoaderIcon)`
  ${folderIconCss}
  color: ${getColor('grey100')};
`;

const TreeLine = styled.div<{$selected: boolean} & AkeneoThemedProps>`
  height: 40px;
  line-height: 40px;
  ${({$selected}) =>
    $selected
      ? css`
          color: ${getColor('blue100')};
        `
      : ''}
`;

const NodeCheckbox = styled(Checkbox)`
  display: inline-block;
  vertical-align: middle;
  margin-right: 8px;
`;

const boxSize = 30;
const ArrowButton = styled.button`
  height: ${boxSize}px;
  width: ${boxSize}px;
  vertical-align: middle;
  margin-right: ${(14 - boxSize) / 2 + 10}px;
  padding: 0;
  border: none;
  background: none;
`;

const LabelWithFolder = styled.button<{$selected: boolean} & AkeneoThemedProps>`
  ${CommonStyle}
  height: ${boxSize}px;
  vertical-align: middle;
  background: none;
  border: none;
  cursor: pointer;
  padding: 0 5px 0 0;
  cursor: pointer;
  ${({$selected}) =>
    $selected
      ? css`
          color: ${getColor('blue100')};
        `
      : ''}
  &:hover {
    ${({$selected}) =>
      !$selected
        ? css`
            color: ${getColor('grey140')};
          `
        : ''}
  }
`;

type TreeProps = {
  children?: ReactNode;
  value: string;
  label: string;
  isLeaf?: boolean;
  selected?: boolean;
  isLoading?: boolean;
  selectable?: boolean;
  readOnly?: boolean;
  onOpen?: (value: string) => void;
  onClose?: (value: string) => void;
  onSelect?: (value: boolean, event: SyntheticEvent) => void;
  onClick?: (value: string) => void;
  _isRoot?: boolean;
};

/**
 * TODO.
 */
const Tree = React.forwardRef<HTMLDivElement, TreeProps>(
  (
    {
      label,
      value,
      children,
      isLeaf = false,
      selected = false,
      isLoading = false,
      selectable = false,
      readOnly = false,
      onSelect,
      onOpen,
      onClose,
      onClick,
      _isRoot = true,
      ...rest
    }: TreeProps,
    forwardedRef: Ref<HTMLDivElement>
  ) => {
    const subTrees: ReactElement<TreeProps>[] = [];
    React.Children.forEach(children, child => {
      if (!isValidElement<TreeProps>(child)) {
        throw new Error(
          `${Tree.displayName || 'Tree'} component only accepts ${Tree.displayName || 'Tree'} as children`
        );
      }
      subTrees.push(child);
    });

    const [isOpen, setOpen] = React.useState<boolean>(subTrees.length > 0);

    const handleOpen = () => {
      setOpen(true);
      if (onOpen) {
        onOpen(value);
      }
    };

    const handleClose = () => {
      setOpen(false);
      if (onClose) {
        onClose(value);
      }
    };

    const handleClick = () => {
      if (onClick) {
        onClick(value);
      } else {
        isOpen ? handleClose() : handleOpen();
      }
    };

    const handleSelect = (checked: CheckboxChecked, event: SyntheticEvent) => {
      if (onSelect) {
        onSelect(checked as boolean, event);
      }
    };

    const result = (
      <TreeContainer role={'treeitem'} aria-expanded={isOpen} ref={forwardedRef} {...rest}>
        <TreeLine $selected={selected}>
          <ArrowButton
            disabled={isLeaf}
            onClick={
              isLeaf
                ? () => {
                    // Do nothing
                  }
                : isOpen
                ? handleClose
                : handleOpen
            }
          >
            {!isLeaf && <TreeArrowIcon $isFolderOpen={isOpen} size={14} />}
          </ArrowButton>

          {selectable && <NodeCheckbox checked={selected} onChange={handleSelect} readOnly={readOnly} />}

          <LabelWithFolder onClick={handleClick} $selected={selected}>
            {isLoading ? (
              <TreeLoaderIcon size={24} />
            ) : isLeaf ? (
              selected ? (
                <TreeFolderPlainIcon size={24} />
              ) : (
                <TreeFolderIcon size={24} />
              )
            ) : selected ? (
              <TreeFoldersPlainIcon size={24} />
            ) : (
              <TreeFoldersIcon size={24} />
            )}
            {label}
          </LabelWithFolder>
        </TreeLine>
        {isOpen && !isLeaf && subTrees.length > 0 && (
          <SubTreesContainer role={'group'}>
            {subTrees.map(subTree => React.cloneElement(subTree, {
              key: subTree.props.value,
              _isRoot: false
            }))}
          </SubTreesContainer>
        )}
      </TreeContainer>
    );

    // https://www.w3.org/WAI/GL/wiki/Using_ARIA_trees
    return _isRoot ?
      <ul role={'tree'}>{result}</ul> :
      result;
  }
);

export {Tree};
