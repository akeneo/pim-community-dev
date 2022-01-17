import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {ImportStructureTab} from './ImportStructureTab';
import {screen, act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {AddDataMappingDropdownProps, InitializeColumnsModalProps} from './components';
import {DataMapping, StructureConfiguration} from './models';

const mockGeneratedColumns = [
  {
    uuid: 'd1249682-720e-11ec-90d6-0242ac120003',
    index: 0,
    label: 'Sku',
  },
];
jest.mock('./components/InitializeColumnsModal', () => ({
  InitializeColumnsModal: ({onConfirm, onCancel}: InitializeColumnsModalProps) => (
    <>
      <button onClick={() => onConfirm(mockGeneratedColumns)}>Generate</button>
      <button onClick={() => onCancel()}>Cancel</button>
    </>
  ),
}));

const mockAddedDataMapping: DataMapping = {
  uuid: 'd1249682-720e-11ec-90d6-0242ac120003',
  target: {
    code: 'name',
    type: 'attribute',
    channel: null,
    locale: null,
    action: 'set',
    ifEmpty: 'skip',
    onError: 'skipLine',
  },
  sources: [],
  operations: [],
  sampleData: [],
};
jest.mock('./components/AddDataMappingDropdown/AddDataMappingDropdown', () => ({
  AddDataMappingDropdown: ({canAddDataMapping, onDataMappingAdded}: AddDataMappingDropdownProps) => (
    <button onClick={() => onDataMappingAdded(mockAddedDataMapping)} disabled={!canAddDataMapping}>
      Add data mapping
    </button>
  ),
}));

const defaultStructureConfiguration: StructureConfiguration = {
  columns: [],
  dataMappings: [],
};

test('it can open the modal to generate columns', async () => {
  const onStructureConfigurationChange = jest.fn();
  renderWithProviders(
    <ImportStructureTab
      structureConfiguration={defaultStructureConfiguration}
      onStructureConfigurationChange={onStructureConfigurationChange}
    />
  );

  await act(async () => {
    await userEvent.click(screen.getByText('akeneo.tailored_import.column_initialization.button'));
    await userEvent.click(screen.getByText('Generate'));
  });

  expect(onStructureConfigurationChange).toHaveBeenCalledWith({
    ...defaultStructureConfiguration,
    columns: mockGeneratedColumns,
  });
});

test('it can open and close the modal', async () => {
  renderWithProviders(
    <ImportStructureTab
      structureConfiguration={defaultStructureConfiguration}
      onStructureConfigurationChange={jest.fn()}
    />
  );

  await act(async () => {
    await userEvent.click(screen.getByText('akeneo.tailored_import.column_initialization.button'));
    await userEvent.click(screen.getByText('Cancel'));
  });

  expect(screen.queryByText('Cancel')).not.toBeInTheDocument();
});

test('it can add a data mapping', () => {
  const onStructureConfigurationChange = jest.fn();
  renderWithProviders(
    <ImportStructureTab
      structureConfiguration={defaultStructureConfiguration}
      onStructureConfigurationChange={onStructureConfigurationChange}
    />
  );

  userEvent.click(screen.getByText('Add data mapping'));

  expect(onStructureConfigurationChange).toHaveBeenCalledWith({
    ...defaultStructureConfiguration,
    dataMappings: [mockAddedDataMapping],
  });
});

test('it cannot add a data mapping when the limit is reached', () => {
  const onStructureConfigurationChange = jest.fn();
  const someDataMappings = Array(500).fill(mockAddedDataMapping);

  renderWithProviders(
    <ImportStructureTab
      structureConfiguration={{...defaultStructureConfiguration, dataMappings: someDataMappings}}
      onStructureConfigurationChange={onStructureConfigurationChange}
    />
  );

  const addDataMappingButton = screen.getByText('Add data mapping');
  expect(addDataMappingButton).toBeDisabled();
});
