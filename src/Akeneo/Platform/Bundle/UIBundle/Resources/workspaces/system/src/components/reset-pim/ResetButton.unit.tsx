import React from 'react';
import userEvent from '@testing-library/user-event';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {ResetButton} from './ResetButton';

test('it opens a modal when clicking on the button', () => {
  renderWithProviders(<ResetButton />);

  expect(screen.queryByText('pim_system.reset_pim.modal.summary.title')).not.toBeInTheDocument();

  userEvent.click(screen.getByText('pim_system.reset_pim.button.label'));

  expect(screen.getByText('pim_system.reset_pim.modal.summary.title')).toBeInTheDocument();
});
