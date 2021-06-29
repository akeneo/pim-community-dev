import React from 'react';
import {act, screen} from '@testing-library/react';
import {CategoryTree, CategoryTreeModel} from '@akeneo-pim-community/shared';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';

const tree: CategoryTreeModel = {
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

test('it render a tree and callback with a child', async () => {
  const childrenCallback = jest.fn();
  const initCallback = jest.fn();
  const isCategorySelected = jest.fn(({id, code, label}) => {
    return id === 4000;
  });
  const init = jest.fn().mockImplementation(async () => {
    return Promise.resolve(tree);
  });

  await act(async () => {
    renderWithProviders(
      <CategoryTree
        childrenCallback={childrenCallback}
        init={init}
        initCallback={initCallback}
        isCategorySelected={isCategorySelected}
      />
    );
  });

  expect(init).toBeCalled();
  expect(initCallback).toBeCalledWith('Tree label', 'Sub tree 1');
  expect(screen.getByText('Tree label')).toBeInTheDocument();
});
