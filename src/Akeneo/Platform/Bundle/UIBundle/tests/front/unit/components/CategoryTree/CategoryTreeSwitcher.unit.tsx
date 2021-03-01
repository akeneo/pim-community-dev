import * as React from 'react';
import {act, fireEvent, screen} from '@testing-library/react';
import '@testing-library/jest-dom';
import {CategoryTreeModel, CategoryTreeSwitcher} from '../../../../../Resources/workspaces/shared';
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

test('it render a tree switcher', async () => {
  const handleClick = jest.fn();

  renderWithProviders(<CategoryTreeSwitcher onClick={handleClick} trees={trees} />);

  expect(screen.getByText('Sales Catalog')).toBeInTheDocument();
});

test('it selects another tree', async () => {
  const handleClick = jest.fn();

  renderWithProviders(<CategoryTreeSwitcher onClick={handleClick} trees={trees} />);

  await act(async () => {
    fireEvent.click(screen.getByText('Sales Catalog'));
  });
  expect(screen.getByText('Master Catalog')).toBeInTheDocument();

  await act(async () => {
    fireEvent.click(screen.getAllByRole('option')[0]);
  });
  expect(handleClick).toBeCalledWith(42);
});
