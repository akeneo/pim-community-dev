import * as React from 'react';
import {act, fireEvent, screen} from '@testing-library/react';
import '@testing-library/jest-dom';
import {CategoryTrees, CategoryTreeModel} from '../../../../../Resources/workspaces/shared';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';

const trees = [
  {
    id: 42,
    code: 'master',
    label: 'Master Catalog',
    selected: false,
  },
  {
    id: 69,
    code: 'sales',
    label: 'Sales Catalog',
    selected: true,
  },
];

const init = jest.fn().mockImplementation(async () => {
  return Promise.resolve(trees);
});
const initTree = jest.fn().mockImplementation(async () => {
  return Promise.resolve({
    label: 'A tree label',
  });
});
const childrenCallback = jest.fn();
const handleCatagoryClick = jest.fn();
const handleTreeChange = jest.fn();
const handleIncludeSubCategoriesChange = jest.fn();
const initCallback = jest.fn();

test('it render trees', async () => {
  await act(async () => {
    renderWithProviders(
      <CategoryTrees
        init={init}
        initTree={initTree}
        childrenCallback={childrenCallback}
        initialSelectedTreeId={42}
        initialIncludeSubCategories={true}
        onCategoryClick={handleCatagoryClick}
        onTreeChange={handleTreeChange}
        onIncludeSubCategoriesChange={handleIncludeSubCategoriesChange}
        initCallback={initCallback}
      />
    );
  });

  expect(screen.getByText('jstree.include_sub')).toBeInTheDocument();
  expect(screen.getByText('jstree.all')).toBeInTheDocument();
  expect(init).toBeCalled();
  expect(initCallback).toBeCalledWith('A tree label', 'jstree.all');
});

test('it selects all categories', async () => {
  await act(async () => {
    renderWithProviders(
      <CategoryTrees
        init={init}
        initTree={initTree}
        childrenCallback={childrenCallback}
        initialSelectedTreeId={42}
        initialIncludeSubCategories={true}
        onCategoryClick={handleCatagoryClick}
        onTreeChange={handleTreeChange}
        onIncludeSubCategoriesChange={handleIncludeSubCategoriesChange}
        initCallback={initCallback}
      />
    );
  });

  await act(async () => {
    fireEvent.click(screen.getByText('jstree.all'));
  });
  expect(handleCatagoryClick).toBeCalledWith(-2, 69, 'jstree.all', 'Sales Catalog');
});

test('it changes tree', async () => {
  await act(async () => {
    renderWithProviders(
      <CategoryTrees
        init={init}
        initTree={initTree}
        childrenCallback={childrenCallback}
        initialSelectedTreeId={42}
        initialIncludeSubCategories={true}
        onCategoryClick={handleCatagoryClick}
        onTreeChange={handleTreeChange}
        onIncludeSubCategoriesChange={handleIncludeSubCategoriesChange}
        initCallback={initCallback}
      />
    );
  });

  await act(async () => {
    fireEvent.click(screen.getByText('Sales Catalog'));
  });
  expect(screen.getByText('Master Catalog')).toBeInTheDocument();

  await act(async () => {
    fireEvent.click(screen.getAllByRole('option')[0]);
  });
  expect(handleTreeChange).toBeCalledWith(42, 'Master Catalog');
});

test('it updates include_sub_categories', async () => {
  await act(async () => {
    renderWithProviders(
      <CategoryTrees
        init={init}
        initTree={initTree}
        childrenCallback={childrenCallback}
        initialSelectedTreeId={42}
        initialIncludeSubCategories={true}
        onCategoryClick={handleCatagoryClick}
        onTreeChange={handleTreeChange}
        onIncludeSubCategoriesChange={handleIncludeSubCategoriesChange}
      />
    );
  });

  expect(screen.getByText('jstree.include_sub')).toBeInTheDocument();
  await act(async () => {
    fireEvent.click(screen.getByText('pim_common.no'));
  });
  expect(handleIncludeSubCategoriesChange).toBeCalledWith(false);
});
