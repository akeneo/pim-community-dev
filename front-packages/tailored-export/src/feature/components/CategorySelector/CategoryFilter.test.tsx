import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {CategoryFilter} from './CategoryFilter';

test('it opens a modal when clicking on the Category Filter button and closes it when cancelling', () => {
  const onCategorySelection = jest.fn();

  renderWithProviders(
    <CategoryFilter filter={{field: 'categories', value: [], operator: 'IN'}} onChange={onCategorySelection} />
  );

  expect(screen.queryByText('pim_connector.export.categories.selector.modal.title')).not.toBeInTheDocument();

  userEvent.click(screen.getByText('pim_common.edit'));

  expect(screen.getByText('pim_connector.export.categories.selector.modal.title')).toBeInTheDocument();

  userEvent.click(screen.getByText('pim_common.cancel'));

  expect(onCategorySelection).not.toHaveBeenCalledWith({field: 'categories', value: [], operator: 'IN'});
  expect(screen.queryByText('pim_connector.export.categories.selector.modal.title')).not.toBeInTheDocument();
});

test('it calls the onCategorySelection callback and closes the modal when confirming', () => {
  const onCategorySelection = jest.fn();

  renderWithProviders(
    <CategoryFilter filter={{field: 'categories', value: [], operator: 'IN'}} onChange={onCategorySelection} />
  );

  expect(screen.queryByText('pim_connector.export.categories.selector.modal.title')).not.toBeInTheDocument();

  userEvent.click(screen.getByText('pim_common.edit'));

  expect(screen.getByText('pim_connector.export.categories.selector.modal.title')).toBeInTheDocument();

  userEvent.click(screen.getByText('pim_common.confirm'));

  expect(onCategorySelection).toHaveBeenCalledWith({field: 'categories', value: [], operator: 'NOT IN'});
  expect(screen.queryByText('pim_connector.export.categories.selector.modal.title')).not.toBeInTheDocument();
});

test('it allow to include children to the selection', () => {
  const onCategorySelection = jest.fn();
  renderWithProviders(
    <CategoryFilter
      filter={{field: 'categories', value: ['category1', 'category2'], operator: 'IN'}}
      onChange={onCategorySelection}
    />
  );

  userEvent.click(screen.getByText('pim_common.edit'));

  userEvent.click(screen.getByText('pim_common.yes'));

  userEvent.click(screen.getByText('pim_common.confirm'));

  expect(onCategorySelection).toHaveBeenCalledWith({
    field: 'categories',
    value: ['category1', 'category2'],
    operator: 'IN CHILDREN',
  });
});

test('it allow to exclude children to the selection', () => {
  const onCategorySelection = jest.fn();
  renderWithProviders(
    <CategoryFilter
      filter={{field: 'categories', value: ['category1', 'category2'], operator: 'IN CHILDREN'}}
      onChange={onCategorySelection}
    />
  );

  userEvent.click(screen.getByText('pim_common.edit'));

  userEvent.click(screen.getByText('pim_common.no'));

  userEvent.click(screen.getByText('pim_common.confirm'));

  expect(onCategorySelection).toHaveBeenCalledWith({
    field: 'categories',
    value: ['category1', 'category2'],
    operator: 'IN',
  });
});

const countPerCategoryTrees = [
  {code: 'master', selectedCategoryCount: '10'},
  {code: 'secondary_tree', selectedCategoryCount: '3'},
];
jest.mock('../../hooks/useCategoryTrees', () => {
  return {useCategoryTrees: () => countPerCategoryTrees};
});
jest.mock('@akeneo-pim-community/shared/lib/hooks/useTranslate', () => ({
  useTranslate: () => {
    return jest.fn((key: string, _: any, count: number) => {
      switch (key) {
        case 'pim_connector.export.categories.selector.label':
          return count;
        default:
          return key;
      }
    });
  },
}));

test('it calculates the total selected categories for all category trees', () => {
  renderWithProviders(
    <CategoryFilter filter={{field: 'categories', value: [], operator: 'IN CHILDREN'}} onChange={() => {}} />
  );
  expect(screen.getByText(13)).toBeInTheDocument();
});
