import * as React from 'react';
import {act, fireEvent, screen} from '@testing-library/react';
import '@testing-library/jest-dom';
import {CategoryTreeModel, RecursiveCategoryTree} from '../../../../../Resources/workspaces/shared';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';

const simpleTree: CategoryTreeModel = {
  id: 42,
  code: 'tree',
  label: 'Tree label',
  selectable: false,
  children: undefined,
};

const treeWithChildren: CategoryTreeModel = {
  id: 42,
  code: 'tree',
  label: 'Tree label',
  selectable: false,
  children: [
    {
      id: 4000,
      code: 'subtree1',
      label: 'Sub tree 1',
      selectable: false,
    },
    {
      id: 69,
      code: 'subtree2',
      label: 'Sub tree 2',
      selectable: false,
    },
  ],
};

test('it render a tree with its children', () => {
  const isCategorySelected = jest.fn();

  renderWithProviders(
    <RecursiveCategoryTree tree={treeWithChildren} childrenCallback={null} isCategorySelected={isCategorySelected} />
  );

  expect(screen.getByText('Tree label')).toBeInTheDocument();
  expect(screen.getByText('Sub tree 1')).toBeInTheDocument();
  expect(screen.getByText('Sub tree 2')).toBeInTheDocument();
});

test('it triggers click', () => {
  const handleClick = jest.fn();

  renderWithProviders(<RecursiveCategoryTree tree={simpleTree} onClick={handleClick} />);

  fireEvent.click(screen.getByText('Tree label'));

  expect(handleClick).toBeCalledWith({code: 'tree', id: 42, label: 'Tree label'});
});

test('it triggers open', async () => {
  const childrenCallback = jest.fn().mockImplementation(async (id: number) => {
    return Promise.resolve([]);
  });

  renderWithProviders(<RecursiveCategoryTree tree={simpleTree} childrenCallback={childrenCallback} />);

  await act(async () => {
    fireEvent.click(screen.getAllByRole('button')[0]);
  });

  expect(childrenCallback).toBeCalledWith(42);
});

test('it triggers change', async () => {
  const handleChange = jest.fn();

  renderWithProviders(<RecursiveCategoryTree tree={{...simpleTree, selectable: true}} onChange={handleChange} />);

  await act(async () => {
    fireEvent.click(screen.getByRole('checkbox'));
  });

  expect(handleChange).toBeCalledWith('tree', true);
});
