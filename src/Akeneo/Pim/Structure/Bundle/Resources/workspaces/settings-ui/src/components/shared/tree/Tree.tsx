import React, {
  isValidElement,
  PropsWithChildren,
  ReactElement,
  ReactNode,
  SyntheticEvent,
  useCallback,
  useRef,
  useState,
} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor, RowIcon, useBooleanState} from 'akeneo-design-system';
import {TreeNode} from '../../../models';
import {
  ArrowButton,
  DragInitiator,
  PlaceholderPosition,
  RowActionsContainer,
  RowInnerContainer,
  TreeArrowIcon,
  TreeRow,
} from './TreeRow';
import {TreeActions} from './TreeActions';
import {TreeIcon} from './TreeIcon';

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
    background: linear-gradient(to top, ${getColor('blue', 40)} 4px, ${getColor('white')} 0px);
    pointer-events: none;
  }
`;
const TreeContainer = styled.li<{isRoot: boolean; placeholderPosition?: PlaceholderPosition} & AkeneoThemedProps>`
  display: block;
  color: ${getColor('grey140')};

  ${({isRoot}) =>
    isRoot &&
    css`
      position: relative;
    `};

  ${({placeholderPosition}) => placeholderPosition && placeholderPositionStyles}
`;

const SubTreesContainer = styled.ul`
  margin: 0 0 0 20px;
  padding: 0;
`;

type CursorPosition = {
  x: number;
  y: number;
};

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
  onDrop?: () => void;
  onDragStart?: () => void;
  onDragEnd?: () => void;
  onDragOver?: (target: Element, cursorPosition: CursorPosition) => void;
  _isRoot?: boolean;
  children?: ReactNode;

  disabled?: boolean;
  draggable?: boolean;
  placeholderPosition?: PlaceholderPosition;
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
  onDragStart,
  onDragEnd,
  onDragOver,
  _isRoot = true,
  disabled = false,
  draggable = false,
  placeholderPosition,
  ...rest
}: PropsWithChildren<TreeProps<T>>) => {
  const subTrees: ReactElement<TreeProps<T>>[] = [];
  let actions: ReactNode | null = null;

  React.Children.forEach(children, child => {
    if (isValidElement(child) && child.type === TreeActions) {
      actions = child;
      return;
    }

    if (!isValidElement<TreeProps<T>>(child)) {
      return;
    }
    subTrees.push(child);
  });

  const dragRef = useRef<HTMLDivElement>(null);
  const treeRowRef = useRef<HTMLDivElement>(null);
  const [isOpen, open, close] = useBooleanState(_isRoot);
  const [timer, setTimer] = useState<number | null>(null);

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
    <TreeContainer
      role="treeitem"
      aria-expanded={isOpen}
      isRoot={_isRoot}
      placeholderPosition={isOpen && placeholderPosition === 'bottom' ? placeholderPosition : undefined}
      {...rest}
    >
      <TreeRow
        ref={treeRowRef}
        onClick={handleClick}
        $selected={selected}
        $disabled={disabled}
        isRoot={_isRoot}
        draggable={draggable}
        placeholderPosition={isOpen && placeholderPosition === 'bottom' ? undefined : placeholderPosition}
        onDragStartCapture={(event: React.DragEvent) => {
          if (event.target !== dragRef.current) {
            event.preventDefault();
          }
        }}
        onDragOver={(event: React.DragEvent) => {
          event.stopPropagation();
          event.preventDefault();
          if (onDragOver && treeRowRef.current) {
            onDragOver(treeRowRef.current, {
              x: event.clientX,
              y: event.clientY,
            });
          }
        }}
        onDragEnter={() => {
          // @fixme does not work when the user enter in a sub element of the row
          if (!isLeaf) {
            if (timer) {
              return;
            }

            const timeoutId = setTimeout(() => {
              handleOpen();
            }, 2000);
            setTimer(timeoutId);
          }
        }}
        onDragLeave={() => {
          // @fixme does not work when the user enter in a sub element of the row
          if (timer !== null) {
            console.log('clear timeout', timer, label);
            clearTimeout(timer);
            setTimer(null);
          }
        }}
        onDrop={(event: React.DragEvent) => {
          event.stopPropagation();
          event.preventDefault();
          event.persist();
          //const identifier = parseInt(event.dataTransfer.getData('text/plain'));

          if (onDrop) {
            onDrop();
          }
          if (timer !== null) {
            console.log('clear timeout', timer, label);
            clearTimeout(timer);
            setTimer(null);
          }
        }}
        onDragEnd={(event: React.DragEvent) => {
          event.stopPropagation();
          event.preventDefault();

          if (onDragEnd) {
            onDragEnd();
          }
          if (timer !== null) {
            console.log('clear timeout', timer, label);
            clearTimeout(timer);
            setTimer(null);
          }
        }}
      >
        <RowInnerContainer>
          {draggable && (
            <DragInitiator
              ref={dragRef}
              draggable
              onDragStart={(event: React.DragEvent) => {
                if (!treeRowRef.current) {
                  return;
                }
                event.dataTransfer.setDragImage(treeRowRef.current, 0, 0);
                //event.dataTransfer.setData('text/plain', value.identifier.toString());

                if (!isLeaf) {
                  handleClose();
                }

                if (onDragStart) {
                  onDragStart();
                }
                // @todo define dragImage with a proper style, call createDragImage
                // @todo if the dragged element is an "opened" parent node, close it with handleClose()
                // @todo call onDragStart
              }}
            >
              <RowIcon size={16} shapeRendering="crispEdges" />
            </DragInitiator>
          )}
          <ArrowButton disabled={isLeaf && !selected} role="button" onClick={handleArrowClick}>
            {(!isLeaf || selected) && <TreeArrowIcon $isFolderOpen={isOpen} size={14} />}
          </ArrowButton>
          <TreeIcon isLoading={isLoading} isLeaf={isLeaf && !selected} selected={selected} />
          {label}
        </RowInnerContainer>
        {actions && !disabled && <RowActionsContainer>{actions}</RowActionsContainer>}
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
TreeActions.displayName = 'Tree.Actions';
Tree.Actions = TreeActions;

export {Tree};
