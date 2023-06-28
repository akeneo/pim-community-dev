import React, {FC, useContext, useEffect} from 'react';
import {renderHook} from '@testing-library/react-hooks';
import {act} from 'react-test-renderer';
import {DraggedNode, TreeNode} from '../models';
import {OrderableTreeContext, OrderableTreeProvider} from '../components';
import {ReorderOnDropHandler, useDropTreeNode} from './useDropTreeNode';
import {aDraggedNode, aTreeNode} from '../../tests/provideTreeNodeHelper';

const InitDraggedNodeWrapper: FC<{draggedNode: DraggedNode | null}> = ({children, draggedNode}) => {
  const {setDraggedNode} = useContext(OrderableTreeContext);
  useEffect(() => {
    setDraggedNode(draggedNode);
  }, [draggedNode, setDraggedNode]);

  return <>{children}</>;
};

const DefaultProviders: FC<{draggedNode: DraggedNode | null}> = ({children, ...context}) => (
  <OrderableTreeProvider isActive={true}>
    <InitDraggedNodeWrapper {...context}>{children}</InitDraggedNodeWrapper>
  </OrderableTreeProvider>
);

const renderUseDropTreeNode = (
  node: TreeNode<any> | undefined,
  reorder: ReorderOnDropHandler,
  draggedNode: DraggedNode | null
) => {
  const wrapper: FC = ({children}) => <DefaultProviders draggedNode={draggedNode}>{children}</DefaultProviders>;

  return renderHook(
    ({node, reorder}: {node: TreeNode<any> | undefined; reorder: ReorderOnDropHandler}) =>
      useDropTreeNode(node, reorder),
    {
      initialProps: {node, reorder},
      wrapper,
    }
  );
};

describe('useDropTreeNode', () => {
  test('it returns default values', () => {
    const reorder = jest.fn();
    const node = aTreeNode('node');
    const {result} = renderUseDropTreeNode(node, reorder, null);

    expect(result.current.dropTarget).toBeNull();
    expect(result.current.placeholderPosition).toBe('none');
    expect(result.current.onDrop).toBeDefined();
    expect(result.current.onDragOver).toBeDefined();
    expect(result.current.onDragEnd).toBeDefined();
    expect(result.current.onDragEnter).toBeDefined();
    expect(result.current.onDragLeave).toBeDefined();
  });

  test('it drags over the node', () => {
    const reorder = jest.fn();
    const node = aTreeNode('node', 1111, [], 'a_node', '', 1, 'node');
    const draggedNode = aDraggedNode(9999);
    const {result} = renderUseDropTreeNode(node, reorder, draggedNode);
    const anElement = document.createElement('div');
    jest.spyOn(anElement, 'getBoundingClientRect').mockImplementation(
      () =>
        ({
          x: 0,
          y: 0,
          width: 100,
          height: 60,
          top: 0,
          right: 100,
          bottom: 60,
          left: 0,
        } as DOMRect)
    );

    // the dragged element could be dropped before the node
    act(() => {
      result.current.onDragEnter();
      result.current.onDragOver(anElement, {x: 1, y: 1});
    });

    expect(result.current.dropTarget).toEqual({
      identifier: 1111,
      parentId: 1,
      position: 'before',
    });
    expect(result.current.placeholderPosition).toBe('top');

    // the dragged element could be dropped inside the node
    act(() => {
      result.current.onDragOver(anElement, {x: 1, y: 21});
    });

    expect(result.current.dropTarget).toEqual({
      identifier: 1111,
      parentId: 1,
      position: 'in',
    });
    expect(result.current.placeholderPosition).toBe('middle');

    // the dragged element could be dropped after the node
    act(() => {
      result.current.onDragOver(anElement, {x: 1, y: 41});
    });

    expect(result.current.dropTarget).toEqual({
      identifier: 1111,
      parentId: 1,
      position: 'after',
    });
    expect(result.current.placeholderPosition).toBe('bottom');

    // The dragged element could leave the node
    act(() => {
      result.current.onDragLeave();
    });

    expect(result.current.placeholderPosition).toBe('none');
  });

  test('it drops on the node', () => {
    const reorder = jest.fn();
    const node = aTreeNode('node', 1111, [], 'a_node', '', 1, 'node');
    const draggedNode = aDraggedNode(9999);
    const {result} = renderUseDropTreeNode(node, reorder, draggedNode);
    const anElement = document.createElement('div');
    jest.spyOn(anElement, 'getBoundingClientRect').mockImplementation(
      () =>
        ({
          x: 0,
          y: 0,
          width: 100,
          height: 60,
          top: 0,
          right: 100,
          bottom: 60,
          left: 0,
        } as DOMRect)
    );

    act(() => {
      result.current.onDragEnter();
      result.current.onDragOver(anElement, {x: 1, y: 1});
    });

    act(() => {
      result.current.onDrop();
      result.current.onDragEnd();
    });

    expect(reorder).toBeCalled();
    expect(result.current.placeholderPosition).toBe('none');
    expect(result.current.dropTarget).toBe(null);
  });
});
