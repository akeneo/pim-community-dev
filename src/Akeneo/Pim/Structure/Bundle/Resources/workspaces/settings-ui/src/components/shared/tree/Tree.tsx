import React, {
  isValidElement,
  PropsWithChildren,
  ReactElement,
  ReactNode,
  SyntheticEvent,
  useCallback,
  useRef,
} from 'react';
import styled from 'styled-components';
import {getColor, useBooleanState, RowIcon} from 'akeneo-design-system';
import {TreeNode} from '../../../models';
import {ArrowButton, TreeArrowIcon, TreeRow} from './TreeRow';

const TreeContainer = styled.li`
  display: block;
  color: ${getColor('grey140')};
`;

const SubTreesContainer = styled.ul`
  margin: 0 0 0 20px;
  padding: 0;
`;

const DragInitiator = styled.div``;

type TreeProps<T> = {
  value: TreeNode<T>;
  label: string;
  isLeaf?: boolean;
  selected?: boolean;
  isLoading?: boolean;
  readOnly?: boolean;
  onOpen?: (value: TreeNode<T>) => void;
  onClose?: (value: TreeNode<T>) => void;
  onChange?: (value: TreeNode<T>, checked: boolean, event: SyntheticEvent) => void;
  onClick?: (value: TreeNode<T>) => void;
  onDrop?: (value: TreeNode<T>, draggedId: number) => void;
  _isRoot?: boolean;
  children?: ReactNode;
};

const Tree = <T,>({
  label,
  value,
  children,
  isLeaf = false,
  selected = false,
  isLoading = false,
  readOnly = false,
  onChange,
  onOpen,
  onClose,
  onClick,
  onDrop,
  _isRoot = true,
  ...rest
}: PropsWithChildren<TreeProps<T>>) => {
  const subTrees: ReactElement<TreeProps<T>>[] = [];
  React.Children.forEach(children, child => {
    if (!isValidElement<TreeProps<T>>(child)) {
      return;
    }
    subTrees.push(child);
  });

  const dragRef = useRef<HTMLDivElement>(null);
  const treeRowRef = useRef<HTMLDivElement>(null);
  const [isOpen, open, close] = useBooleanState(_isRoot);

  const handleOpen = useCallback(() => {
    open();
    if (onOpen) {
      onOpen(value);
    }
  }, [onOpen, value]);

  const handleClose = useCallback(() => {
    close();
    if (onClose) {
      onClose(value);
    }
  }, [onClose, value]);

  const handleArrowClick = useCallback(
    event => {
      event.stopPropagation();

      if (isLeaf) {
        return;
      }

      isOpen ? handleClose() : handleOpen();
    },
    [isOpen, handleClose, handleOpen, isLeaf]
  );

  const handleClick = useCallback(
    event => {
      if (onClick) {
        onClick(value);
      } else {
        handleArrowClick(event);
      }
    },
    [handleArrowClick, onClick, value]
  );

  // https://www.w3.org/WAI/GL/wiki/Using_ARIA_trees
  const result = (
    <TreeContainer role="treeitem" aria-expanded={isOpen} {...rest}>
      <TreeRow
        ref={treeRowRef}
        onClick={handleClick}
        $selected={selected}
        draggable
        onDragStartCapture={event => {
          if (event.target !== dragRef.current) {
            event.preventDefault();
          }
        }}
        onDragOver={event => {
          event.stopPropagation();
          event.preventDefault();
          console.log(`dragover ${label}`, event)
        }}
        onDrop={event => {
          event.stopPropagation();
          event.preventDefault();
          event.persist();
          const identifier = parseInt(event.dataTransfer.getData('text/plain'));
          console.log(`drop ${identifier} aside ${value.identifier} in parent ${value.parent}`);

          if (onDrop) {
            onDrop(value, identifier);
          }
          // @todo drop over parent node =>
          // @todo drop over leaf node =>

        }}
      >
        <DragInitiator
          ref={dragRef}
          draggable
          onDragStart={event => {
            if (!treeRowRef.current) {
              return;
            }
            event.dataTransfer.setDragImage(treeRowRef.current, 0, 0);
            event.dataTransfer.setData('text/plain', value.identifier.toString());
          }}
        >
          <RowIcon size={16} />
        </DragInitiator>
        <ArrowButton disabled={isLeaf} role="button" onClick={handleArrowClick}>
          {!isLeaf && <TreeArrowIcon $isFolderOpen={isOpen} size={14} />}
        </ArrowButton>
        {label}
      </TreeRow>
      {isOpen && !isLeaf && subTrees.length > 0 && (
        <SubTreesContainer role="group">
          {subTrees.map(subTree =>
            React.cloneElement(subTree, {
              key: JSON.stringify(subTree.props.value),
              _isRoot: false,
            })
          )}
        </SubTreesContainer>
      )}
    </TreeContainer>
  );

  return _isRoot ? <ul role="tree">{result}</ul> : result;
};

Tree.displayName = 'Tree';

export {Tree};
