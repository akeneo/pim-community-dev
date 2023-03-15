import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '../tests';
import {DoubleCheckDeleteModal} from './DoubleCheckDeleteModal';
import userEvent from '@testing-library/user-event';

test('It displays a double check delete modal', () => {
  renderWithProviders(
    <DoubleCheckDeleteModal
      title="Entity"
      onCancel={jest.fn()}
      onConfirm={jest.fn()}
      doubleCheckInputLabel="a_double_check_input_label"
      textToCheck="delete"
    >
      Are you sure you want to remove this entity ?
    </DoubleCheckDeleteModal>
  );

  expect(screen.getByText('Are you sure you want to remove this entity ?')).toBeInTheDocument();
  expect(screen.getByLabelText('a_double_check_input_label')).toBeInTheDocument();
  expect(screen.getByText('pim_common.delete')).toBeDisabled();
});

test('It allows deletion when user type the text to check', () => {
  const handleConfirm = jest.fn();

  renderWithProviders(
    <DoubleCheckDeleteModal
      title="Entity"
      onCancel={jest.fn()}
      onConfirm={handleConfirm}
      doubleCheckInputLabel="a_double_check_input_label"
      textToCheck="delete"
    >
      Are you sure you want to remove this entity ?
    </DoubleCheckDeleteModal>
  );

  userEvent.type(screen.getByLabelText('a_double_check_input_label'), 'delete');
  expect(screen.getByText('pim_common.delete')).toBeEnabled();

  userEvent.click(screen.getByText('pim_common.delete'));
  expect(handleConfirm).toBeCalled();
});

test('It does not call onConfirm when user press enter and the text is not confirmed', () => {
  const handleConfirm = jest.fn();

  renderWithProviders(
    <DoubleCheckDeleteModal
      title="Entity"
      onCancel={jest.fn()}
      onConfirm={handleConfirm}
      doubleCheckInputLabel="a_double_check_input_label"
      textToCheck="delete"
    >
      Are you sure you want to remove this entity ?
    </DoubleCheckDeleteModal>
  );

  const input = screen.getByLabelText('a_double_check_input_label');
  userEvent.type(input, 'eteled{enter}');

  expect(handleConfirm).not.toBeCalled();
});
