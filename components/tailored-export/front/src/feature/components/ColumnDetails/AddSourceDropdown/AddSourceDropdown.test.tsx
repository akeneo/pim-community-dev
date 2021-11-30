import React from 'react';
import {screen, act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {AddSourceDropdown} from './AddSourceDropdown';

jest.mock('../../../hooks/useAvailableSourcesFetcher', () => ({
  useAvailableSourcesFetcher: () => () => ({
    results: [
      {
        code: 'system',
        label: 'System',
        children: [
          {
            code: 'category',
            label: 'Categories',
            type: 'property',
          },
          {
            code: 'enabled',
            label: 'Activé',
            type: 'property',
          },
        ],
      },
      {
        code: 'marketing',
        label: 'Marketing',
        children: [
          {
            code: 'name',
            label: 'Nom',
            type: 'attribute',
          },
          {
            code: 'description',
            label: 'Description',
            type: 'attribute',
          },
        ],
      },
    ],
  }),
}));

test('it adds attribute source', async () => {
  const handleSourceSelected = jest.fn();

  renderWithProviders(<AddSourceDropdown canAddSource={true} onSourceSelected={handleSourceSelected} />);
  await act(async () => {
    userEvent.click(screen.getByText('akeneo.tailored_export.column_details.sources.add'));
  });

  expect(screen.getByText('System')).toBeInTheDocument();
  expect(screen.getByText('Categories')).toBeInTheDocument();
  expect(screen.getByText('Activé')).toBeInTheDocument();
  expect(screen.getByText('Marketing')).toBeInTheDocument();
  expect(screen.getByText('Nom')).toBeInTheDocument();
  expect(screen.getByText('Description')).toBeInTheDocument();

  userEvent.click(screen.getByText('Nom'));
  expect(handleSourceSelected).lastCalledWith('name', 'attribute');
});

test('it adds property source', async () => {
  const handleSourceSelected = jest.fn();

  renderWithProviders(<AddSourceDropdown canAddSource={true} onSourceSelected={handleSourceSelected} />);
  await act(async () => {
    userEvent.click(screen.getByText('akeneo.tailored_export.column_details.sources.add'));
  });

  userEvent.click(screen.getByText('Categories'));
  expect(handleSourceSelected).lastCalledWith('category', 'property');
});

test('it cannot add a source when the limit is reached', () => {
  const handleSourceSelected = jest.fn();

  renderWithProviders(<AddSourceDropdown canAddSource={false} onSourceSelected={handleSourceSelected} />);

  const addSourceButton = screen.getByText('akeneo.tailored_export.column_details.sources.add');
  expect(addSourceButton).toHaveAttribute('disabled');
  expect(addSourceButton).toHaveAttribute(
    'title',
    'akeneo.tailored_export.validation.sources.max_source_count_reached'
  );

  userEvent.click(addSourceButton);
  expect(handleSourceSelected).not.toHaveBeenCalled();
});
