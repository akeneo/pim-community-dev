import React from 'react';
import {act, screen, within} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {CategorySelector} from './CategorySelector';

const getRootChildren = (count: number) => [
  {
    attr: {id: 'node_0', 'data-code': `child-${count}`},
    data: `child ${count}`,
    state: 'closed',
  },
];

const category = {
  id: 1,
  code: 'master',
  parent: null,
  labels: {en_US: 'Master'},
};

beforeEach(() => {
  let childrenFetchCount = 0;
  global.fetch = jest.fn().mockImplementation(async (route: string) => ({
    ok: true,
    json: async () => {
      switch (route) {
        case 'pim_enrich_categorytree_children':
          return getRootChildren(childrenFetchCount++);
        case 'pim_enrich_category_rest_get':
          return category;
        default:
          throw new Error('Unexpected fetch call');
      }
    },
  }));
});

test('it displays a Category tree with its children categories', async () => {
  const onChange = jest.fn();
  const initialCategoryCodes = ['webcam', 'scanners'];

  await act(async () => {
    renderWithProviders(
      <CategorySelector
        categoryTreeCode="root"
        shouldIncludeSubCategories={false}
        initialCategoryCodes={initialCategoryCodes}
        onChange={onChange}
      />
    );
  });

  // Opening first tree
  userEvent.click(screen.getAllByRole('button')[0]);
  userEvent.click(screen.getAllByRole('button')[0]);

  const treeItems = screen.getAllByRole('treeitem');

  await act(async () => {
    // Opening second tree by finding the arrow button inside
    userEvent.click(within(treeItems[1]).getAllByRole('button')[0]);
  });

  expect(screen.getByText('Master')).toBeInTheDocument();
  expect(screen.getByText('child 0')).toBeInTheDocument();
  expect(screen.getByText('child 1')).toBeInTheDocument();
});

test('it can select then unselect a Category tree', async () => {
  const onChange = jest.fn();
  const initialCategoryCodes = ['webcam', 'scanners'];

  await act(async () => {
    renderWithProviders(
      <CategorySelector
        categoryTreeCode="root"
        shouldIncludeSubCategories={false}
        initialCategoryCodes={initialCategoryCodes}
        onChange={onChange}
      />
    );
  });

  const treeItems = screen.getAllByRole('treeitem');

  // Selecting the child 0
  userEvent.click(within(treeItems[1]).getByRole('checkbox'));

  expect(onChange).toHaveBeenCalledWith(['webcam', 'scanners', 'child-0']);

  // Unselecting the child 0
  userEvent.click(within(treeItems[1]).getByRole('checkbox'));

  expect(onChange).toHaveBeenCalledWith(['webcam', 'scanners']);
});

test('it can select a category and its children when included', async () => {
  const onChange = jest.fn();
  const initialCategoryCodes = ['webcam', 'scanners'];

  await act(async () => {
    renderWithProviders(
      <CategorySelector
        categoryTreeCode="root"
        shouldIncludeSubCategories={true}
        initialCategoryCodes={initialCategoryCodes}
        onChange={onChange}
      />
    );
  });

  const treeItems = screen.getAllByRole('treeitem');

  // Selecting the child 0
  const [, firstChildTree] = screen.getAllByRole('treeitem');
  const firstChildTreeCheckbox = within(firstChildTree).getByRole('checkbox');
  userEvent.click(firstChildTreeCheckbox);
  expect(onChange).toHaveBeenCalledWith(['webcam', 'scanners', 'child-0']);

  // Displaying children child 1 as selected
  await act(async () => userEvent.click(within(treeItems[1]).getByTitle('child 0')));
  expect(screen.getByTitle('child 1')).toHaveAttribute('aria-selected', 'true');

  // Unselecting the child 0
  userEvent.click(firstChildTreeCheckbox);
  expect(onChange).toHaveBeenCalledWith(['webcam', 'scanners']);

  // Displaying children child 1 as not selected
  expect(screen.getByTitle('child 1')).toHaveAttribute('aria-selected', 'false');
});
