import React from 'react';
import {screen, act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from 'feature/tests';
import {AddDataMappingDropdown} from './AddDataMappingDropdown';

const mockUuid = 'd1249682-720e-11ec-90d6-0242ac120003';
jest.mock('akeneo-design-system', () => ({
  ...jest.requireActual('akeneo-design-system'),
  uuid: () => mockUuid,
}));

jest.mock('../../hooks/useAvailableTargetsFetcher', () => ({
  useAvailableTargetsFetcher: () => () => ({
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

test('it adds data mapping with attribute target', async () => {
  const handleDataMappingAdded = jest.fn();

  await renderWithProviders(
    <AddDataMappingDropdown canAddDataMapping={true} onDataMappingAdded={handleDataMappingAdded} />
  );

  await act(async () => {
    userEvent.click(screen.getByText('akeneo.tailored_import.data_mapping_list.add'));
  });

  expect(screen.getByText('System')).toBeInTheDocument();
  expect(screen.getByText('Categories')).toBeInTheDocument();
  expect(screen.getByText('Activé')).toBeInTheDocument();
  expect(screen.getByText('Marketing')).toBeInTheDocument();
  expect(screen.getByText('Nom')).toBeInTheDocument();
  expect(screen.getByText('Description')).toBeInTheDocument();

  await act(async () => {
    userEvent.click(screen.getByText('Nom'));
  });

  expect(handleDataMappingAdded).lastCalledWith({
    uuid: mockUuid,
    target: {
      code: 'name',
      type: 'attribute',
      action_if_not_empty: 'set',
      action_if_empty: 'skip',
      channel: null,
      locale: null,
      source_configuration: null,
    },
    sources: [],
    operations: [],
    sample_data: [],
  });
});

test('it adds data mapping with property target', async () => {
  const handleDataMappingAdded = jest.fn();

  await renderWithProviders(
    <AddDataMappingDropdown canAddDataMapping={true} onDataMappingAdded={handleDataMappingAdded} />
  );

  await act(async () => {
    userEvent.click(screen.getByText('akeneo.tailored_import.data_mapping_list.add'));
  });

  userEvent.click(screen.getByText('Categories'));
  expect(handleDataMappingAdded).lastCalledWith({
    uuid: mockUuid,
    target: {
      code: 'category',
      type: 'property',
      action_if_not_empty: 'set',
      action_if_empty: 'skip',
    },
    sources: [],
    operations: [],
    sample_data: [],
  });
});

test('it cannot add a data mapping when the limit is reached', async () => {
  const handleDataMappingAdded = jest.fn();

  await renderWithProviders(
    <AddDataMappingDropdown canAddDataMapping={false} onDataMappingAdded={handleDataMappingAdded} />
  );

  const addDataMappingButton = screen.getByText('akeneo.tailored_import.data_mapping_list.add');
  expect(addDataMappingButton).toHaveAttribute('disabled');
  expect(addDataMappingButton).toHaveAttribute(
    'title',
    'akeneo.tailored_import.validation.data_mappings.max_data_mapping_count_reached'
  );

  userEvent.click(addDataMappingButton);
  expect(handleDataMappingAdded).not.toHaveBeenCalled();
});
