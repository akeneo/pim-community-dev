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
    operator: 'IN CHILDREN LIST'
  });
});

test('it allow to exclude children to the selection', () => {
  const onCategorySelection = jest.fn();
  renderWithProviders(
    <CategoryFilter
      filter={{field: 'categories', value: ['category1', 'category2'], operator: 'IN CHILDREN LIST'}}
      onChange={onCategorySelection}
    />
  );

  userEvent.click(screen.getByText('pim_common.edit'));

  userEvent.click(screen.getByText('pim_common.no'));

  userEvent.click(screen.getByText('pim_common.confirm'));

  expect(onCategorySelection).toHaveBeenCalledWith({
    field: 'categories',
    value: ['category1', 'category2'],
    operator: 'IN'
  });
});
