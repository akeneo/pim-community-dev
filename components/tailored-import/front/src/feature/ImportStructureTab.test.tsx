import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {ImportStructureTab} from './ImportStructureTab';
import {screen, act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {InitializeColumnsModalProps, DataMappingListProps} from './components';
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

const mockCreatedDataMapping: DataMapping = {
  uuid: 'd1249682-720e-11ec-90d6-0242ac120003',
  target: {
    code: 'name',
    type: 'attribute',
    channel: null,
    locale: null,
    action: 'set',
    if_empty: 'skip',
  },
  sources: [],
  operations: [],
  sample_data: [],
};

let mockUuid = 0;
jest.mock('akeneo-design-system', () => ({
  ...jest.requireActual('akeneo-design-system'),
  uuid: () => `uuid_${++mockUuid}`,
}));

jest.mock('./components/DataMappingList/DataMappingList', () => ({
  DataMappingList: ({onDataMappingAdded, onDataMappingSelected}: DataMappingListProps) => (
    <>
      <button onClick={() => onDataMappingAdded(mockCreatedDataMapping)}>Add data mapping</button>
      <button onClick={() => onDataMappingSelected(`uuid_${mockUuid}`)}>Display data mapping</button>
    </>
  ),
}));

jest.mock('./components/DataMappingDetails/DataMappingDetails', () => ({
  DataMappingDetails: ({
    dataMapping,
    onDataMappingChange,
  }: {
    dataMapping: DataMapping;
    onDataMappingChange: (dataMapping: DataMapping) => void;
  }) => (
    <>
      Data mapping details of {dataMapping.uuid}
      <button onClick={() => onDataMappingChange({...dataMapping, target: {...dataMapping.target, if_empty: 'clear'}})}>
        Update data mapping
      </button>
    </>
  ),
}));

const defaultStructureConfiguration: StructureConfiguration = {
  columns: [],
  data_mappings: [],
};

const getDefaultIdentifierDataMapping = (): DataMapping => ({
  uuid: `uuid_${mockUuid}`,
  target: {
    code: 'sku',
    type: 'attribute',
    channel: null,
    locale: null,
    action: 'set',
    if_empty: 'skip',
  },
  sources: ['d1249682-720e-11ec-90d6-0242ac120003'],
  operations: [],
  sample_data: [],
});

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
    data_mappings: [getDefaultIdentifierDataMapping()],
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

test('it can add data mapping from the column list', () => {
  const onStructureConfigurationChange = jest.fn();
  const defaultIdentifierDataMapping = getDefaultIdentifierDataMapping();
  const structureConfiguration = {
    ...defaultStructureConfiguration,
    columns: [
      {
        uuid: 'd1249682-720e-11ec-90d6-0242ac120003',
        index: 0,
        label: 'Sku',
      },
    ],
    data_mappings: [defaultIdentifierDataMapping],
  };

  renderWithProviders(
    <ImportStructureTab
      structureConfiguration={structureConfiguration}
      onStructureConfigurationChange={onStructureConfigurationChange}
    />
  );

  userEvent.click(screen.getByText('Add data mapping'));

  expect(onStructureConfigurationChange).toHaveBeenCalledWith({
    ...structureConfiguration,
    data_mappings: [defaultIdentifierDataMapping, mockCreatedDataMapping],
  });
});

test('it can display and update the data mapping detail when clicking on the data mapping', () => {
  const handleStructureConfigurationChange = jest.fn();
  const defaultIdentifierDataMapping = getDefaultIdentifierDataMapping();
  const structureConfiguration = {
    columns: [
      {
        uuid: 'd1249682-720e-11ec-90d6-0242ac120003',
        index: 0,
        label: 'Sku',
      },
    ],
    data_mappings: [defaultIdentifierDataMapping],
  };

  renderWithProviders(
    <ImportStructureTab
      structureConfiguration={structureConfiguration}
      onStructureConfigurationChange={handleStructureConfigurationChange}
    />
  );

  expect(screen.queryByText('Data mapping details of uuid_1')).not.toBeInTheDocument();

  userEvent.click(screen.getByText('Display data mapping'));

  expect(screen.getByText('Data mapping details of uuid_1')).toBeInTheDocument();
  userEvent.click(screen.getByText('Update data mapping'));

  expect(handleStructureConfigurationChange).toHaveBeenCalledWith({
    ...structureConfiguration,
    data_mappings: [
      {
        uuid: `uuid_${mockUuid}`,
        target: {
          code: 'sku',
          type: 'attribute',
          channel: null,
          locale: null,
          action: 'set',
          if_empty: 'clear',
        },
        operations: [],
        sample_data: [],
        sources: ['d1249682-720e-11ec-90d6-0242ac120003'],
      },
    ],
  });
});
