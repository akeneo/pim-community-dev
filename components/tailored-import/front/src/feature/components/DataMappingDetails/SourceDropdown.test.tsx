import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {screen, act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {SourceDropdown} from './SourceDropdown';

test('it can add a source', () => {
  const onColumnSelected = jest.fn();
  const columns = [
    {
      uuid: 'd1249682-720e-11ec-90d6-0242ac120003',
      index: 0,
      label: 'Sku',
    },
    {
      uuid: 'd1249682-720e-31ec-90d6-0242ac120003',
      index: 1,
      label: 'Product',
    },
  ];

  renderWithProviders(<SourceDropdown columns={columns} onColumnSelected={onColumnSelected} />);

  userEvent.click(screen.getByText('akeneo.tailored_import.data_mapping.sources.add.label'));
  userEvent.click(screen.getByText('Product (B)'));

  expect(onColumnSelected).toHaveBeenCalledWith({
    uuid: 'd1249682-720e-31ec-90d6-0242ac120003',
    index: 1,
    label: 'Product',
  });
});

test('it can filter sources with a text search', () => {
  jest.useFakeTimers();

  const onColumnSelected = jest.fn();
  const columns = [
    {
      uuid: 'd1249682-720e-11ec-90d6-0242ac120003',
      index: 0,
      label: 'Sku',
    },
    {
      uuid: 'd1249682-720e-31ec-90d6-0242ac120003',
      index: 1,
      label: 'Product',
    },
  ];

  renderWithProviders(<SourceDropdown columns={columns} onColumnSelected={onColumnSelected} />);

  userEvent.click(screen.getByText('akeneo.tailored_import.data_mapping.sources.add.label'));

  userEvent.type(screen.getByPlaceholderText('pim_common.search'), 'Sku');
  act(() => {
    jest.runAllTimers();
  });

  expect(screen.queryByText('Sku (A)')).toBeInTheDocument();
  expect(screen.queryByText('Product (B)')).not.toBeInTheDocument();

  userEvent.type(screen.getByPlaceholderText('pim_common.search'), 'Unknown');

  act(() => {
    jest.runAllTimers();
  });

  expect(screen.queryByText('pim_common.no_result')).toBeInTheDocument();
});
