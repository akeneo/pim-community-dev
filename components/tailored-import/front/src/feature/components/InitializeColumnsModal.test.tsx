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

test('it can close the modal', () => {
  const onCancel = jest.fn();
  renderWithProviders(<InitializeColumnsModal onConfirm={jest.fn()} onCancel={onCancel} />);

  const closeButton = screen.getByTitle('pim_common.close');
  userEvent.click(closeButton);
  expect(onCancel).toHaveBeenCalled();
});
