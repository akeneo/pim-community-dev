import React from 'react';
import userEvent from '@testing-library/user-event';
import {act, screen} from '@testing-library/react';
import {NotificationLevel, renderWithProviders} from '@akeneo-pim-community/shared';
import {ResetButton} from './ResetButton';

const mockedNotify = jest.fn();
jest.mock('@akeneo-pim-community/shared', () => ({
  ...jest.requireActual('@akeneo-pim-community/shared'),
  useNotify: () => mockedNotify,
}));

beforeEach(() => {
  mockedNotify.mockClear();
});

let mockedCheckInstance = jest.fn();
jest.mock('../../hooks/useCheckInstanceCanBeReset', () => ({
  useCheckInstanceCanBeReset: () => [false, mockedCheckInstance],
}));

test('it opens a modal when clicking on the button and if the PIM can be reset', async () => {
  renderWithProviders(<ResetButton />);

  expect(screen.queryByText('pim_system.reset_pim.modal.summary.title')).not.toBeInTheDocument();

  await act(async () => {
    userEvent.click(screen.getByText('pim_system.reset_pim.button.label'));
  });

  expect(screen.getByText('pim_system.reset_pim.modal.summary.title')).toBeInTheDocument();
});

test('it notifies when the reset PIM cannot happen', async () => {
  mockedCheckInstance = jest.fn(() => {
    throw new Error('Some jobs are still running');
  });

  renderWithProviders(<ResetButton />);

  await act(async () => {
    userEvent.click(screen.getByText('pim_system.reset_pim.button.label'));
  });

  expect(mockedNotify).toHaveBeenCalledWith(NotificationLevel.ERROR, 'pim_system.reset_pim.jobs_running');
});
