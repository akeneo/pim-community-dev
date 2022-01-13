import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {InitializeColumnsModal} from './InitializeColumnsModal';
import {screen, act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';

const mockUuid = 'd1249682-720e-11ec-90d6-0242ac120003';
jest.mock('akeneo-design-system', () => ({
  ...jest.requireActual('akeneo-design-system'),
  uuid: () => mockUuid,
}));

test('it can paste sheet data to create columns', async () => {
  const onConfirm = jest.fn();
  renderWithProviders(<InitializeColumnsModal onConfirm={onConfirm} onCancel={jest.fn()} />);

  const confirmButton = screen.getByText('pim_common.confirm');
  const sheetDataTextarea = screen.getByRole('textbox');

  await act(async () => {
    await userEvent.paste(sheetDataTextarea, 'Ref\tDesignation\n6363562\tMotor');
    await userEvent.click(confirmButton);
  });

  expect(onConfirm).toHaveBeenCalledWith([
    {
      uuid: mockUuid,
      index: 0,
      label: 'Ref',
    },
    {
      uuid: mockUuid,
      index: 1,
      label: 'Designation',
    },
  ]);
});

test('it display a warning message when columns exceeded the limit', async () => {
  const onConfirm = jest.fn();
  renderWithProviders(<InitializeColumnsModal onConfirm={onConfirm} onCancel={jest.fn()} />);

  const confirmButton = screen.getByText('pim_common.confirm');
  const sheetDataTextarea = screen.getByRole('textbox');
  const columns = new Array(501).fill('').map((value, index) => `column${index}`);
  await act(async () => {
    await userEvent.paste(sheetDataTextarea, columns.join('\t'));
  });

  expect(screen.getByText('akeneo.tailored_import.column_initialization.max_column_count_reached')).toBeInTheDocument();
  await act(async () => {
    await userEvent.click(confirmButton);
  });

  expect(onConfirm).toHaveBeenCalledWith(
    Array(500).fill('').map((value, index) => ({
      uuid: mockUuid,
      index: index,
      label: `column${index}`,
    }))
  );
});

test('it can close the modal', () => {
  const onCancel = jest.fn();
  renderWithProviders(<InitializeColumnsModal onConfirm={jest.fn()} onCancel={onCancel} />);

  const closeButton = screen.getByTitle('pim_common.close');
  userEvent.click(closeButton);
  expect(onCancel).toHaveBeenCalled();
});
