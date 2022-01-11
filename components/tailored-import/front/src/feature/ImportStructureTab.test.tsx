import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {ImportStructureTab} from './ImportStructureTab';
import {screen, act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {InitializeColumnsModalProps} from './components';

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

test('it can open the modal to generate columns', async () => {
  const onStructureConfigurationChange = jest.fn();
  renderWithProviders(
    <ImportStructureTab
      structureConfiguration={{columns: []}}
      onStructureConfigurationChange={onStructureConfigurationChange}
    />
  );

  await act(async () => {
    await userEvent.click(screen.getByText('akeneo.tailored_import.column_initialization.button'));
    await userEvent.click(screen.getByText('Generate'));
  });

  expect(onStructureConfigurationChange).toHaveBeenCalledWith({columns: mockGeneratedColumns});
});

test('it can open and close the modal', async () => {
  renderWithProviders(
    <ImportStructureTab structureConfiguration={{columns: []}} onStructureConfigurationChange={jest.fn()} />
  );

  await act(async () => {
    await userEvent.click(screen.getByText('akeneo.tailored_import.column_initialization.button'));
    await userEvent.click(screen.getByText('Cancel'));
  });

  expect(screen.queryByText('Cancel')).not.toBeInTheDocument();
});
