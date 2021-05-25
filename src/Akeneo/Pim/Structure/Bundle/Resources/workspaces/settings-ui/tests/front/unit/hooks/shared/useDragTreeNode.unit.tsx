import React, {FC} from 'react';
import {TreeNode, useDragTreeNode} from '../../../../../src';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {renderHook} from '@testing-library/react-hooks';
import {OrderableTreeProvider} from '../../../../../src/components/shared/providers/OrderableTreeProvider';
import {aTreeNode} from '../../../utils/provideTreeNodeHelper';
import {act} from 'react-test-renderer';

const DefaultProviders: FC<{orderable: boolean}> = ({children, orderable}) => (
  <DependenciesProvider>
    <ThemeProvider theme={pimTheme}>
      <OrderableTreeProvider isActive={orderable}>{children}</OrderableTreeProvider>
    </ThemeProvider>
  </DependenciesProvider>
);

const renderUseDragTreeNode = (node: TreeNode<any> | undefined, index: number, orderable: boolean) => {
  const wrapper: FC = ({children}) => <DefaultProviders orderable={orderable}>{children}</DefaultProviders>;

  return renderHook(({node, index}: {node: TreeNode<any> | undefined; index: number}) => useDragTreeNode(node, index), {
    initialProps: {node, index},
    wrapper,
  });
};

describe('useDragTreeNode', () => {
  test('it returns default values', () => {
    const node = aTreeNode('node');
    const {result} = renderUseDragTreeNode(node, 0, true);

    expect(result.current.onDragEnd).toBeDefined();
    expect(result.current.onDragStart).toBeDefined();
    expect(result.current.isDragged).toBeDefined();
  });

  test('it tests the node is draggable when reorder is active', () => {
    const root = aTreeNode('a_root', 1111, [2222], 'a_root', null, 'root');
    const node = aTreeNode('a_node', 2222, [3333], 'a_node', 1111, 'node');
    const leaf = aTreeNode('a_leaf', 3333, [], 'a_leaf', 2222, 'leaf');

    const {result, rerender} = renderUseDragTreeNode(root, 0, true);

    expect(result.current.isDraggable).toBeFalsy();

    rerender({node, index: 0});
    expect(result.current.isDraggable).toBeTruthy();

    rerender({node: leaf, index: 0});
    expect(result.current.isDraggable).toBeTruthy();
  });

  test('it tests the node is draggable when reorder is not active', () => {
    const root = aTreeNode('a_root', 1111, [2222], 'a_root', null, 'root');
    const node = aTreeNode('a_node', 2222, [3333], 'a_node', 1111, 'node');
    const leaf = aTreeNode('a_leaf', 3333, [], 'a_leaf', 2222, 'leaf');

    const {result, rerender} = renderUseDragTreeNode(root, 0, false);

    expect(result.current.isDraggable).toBeFalsy();

    rerender({node, index: 0});
    expect(result.current.isDraggable).toBeFalsy();

    rerender({node: leaf, index: 0});
    expect(result.current.isDraggable).toBeFalsy();
  });

  test('it drags the node', () => {
    const node = aTreeNode('a_node', 1234, [], 'a_node', 1, 'node');

    const {result} = renderUseDragTreeNode(node, 0, true);

    expect(result.current.isDragged()).toBeFalsy();

    act(() => {
      result.current.onDragStart();
    });

    expect(result.current.isDragged()).toBeTruthy();

    act(() => {
      result.current.onDragEnd();
    });

    expect(result.current.isDragged()).toBeFalsy();
  });

  test('it does not drag when the node is undefined', () => {
    const {result} = renderUseDragTreeNode(undefined, 0, true);

    expect(result.current.isDragged()).toBeFalsy();

    act(() => {
      result.current.onDragStart();
    });

    expect(result.current.isDragged()).toBeFalsy();

    act(() => {
      result.current.onDragEnd();
    });

    expect(result.current.isDragged()).toBeFalsy();
  });
});
