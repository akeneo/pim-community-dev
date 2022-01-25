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
    ifEmpty: 'skip',
    onError: 'skipLine',
  },
  sources: [],
  operations: [],
  sampleData: [],
};
jest.mock('./components/DataMappingList/DataMappingList', () => ({
  DataMappingList: ({onDataMappingAdded}: DataMappingListProps) => (
    <button onClick={() => onDataMappingAdded(mockCreatedDataMapping)}>Data mapping list</button>
  ),
}));

const defaultStructureConfiguration: StructureConfiguration = {
  columns: [],
  dataMappings: [],
};

let mockUuid = 0;
jest.mock('akeneo-design-system', () => ({
  ...jest.requireActual('akeneo-design-system'),
  uuid: () => `uuid_${++mockUuid}`,
}));

const getDefaultIdentifierDataMapping = (): DataMapping => ({
  uuid: `uuid_${mockUuid}`,
  target: {
    code: 'sku',
    type: 'attribute',
    channel: null,
    locale: null,
    action: 'set',
    ifEmpty: 'skip',
    onError: 'skipLine',
  },
  sources: ['d1249682-720e-11ec-90d6-0242ac120003'],
  operations: [],
  sampleData: [],
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
    dataMappings: [getDefaultIdentifierDataMapping()],
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

test('it can add data mapping from the list', () => {
  const onStructureConfigurationChange = jest.fn();
  const defaultIdentifierDataMapping = getDefaultIdentifierDataMapping();

  renderWithProviders(
    <ImportStructureTab
      structureConfiguration={{
        ...defaultStructureConfiguration,
        dataMappings: [defaultIdentifierDataMapping],
      }}
      onStructureConfigurationChange={onStructureConfigurationChange}
    />
  );

  userEvent.click(screen.getByText('Data mapping list'));

  expect(onStructureConfigurationChange).toHaveBeenCalledWith({
    ...defaultStructureConfiguration,
    dataMappings: [defaultIdentifierDataMapping, mockCreatedDataMapping],
  });
});
