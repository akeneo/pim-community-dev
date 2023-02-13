import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '../tests';
import {DoubleCheckDeleteModal} from "./DoubleCheckDeleteModal";
import userEvent from "@testing-library/user-event";

test('It displays a double check delete modal', () => {
  renderWithProviders(
    <DoubleCheckDeleteModal
      title="Entity"
      onCancel={jest.fn()}
      onConfirm={jest.fn()}
      textToCheck="delete"
    >
      Are you sure you want to remove this entity ?
    </DoubleCheckDeleteModal>
  );

  expect(screen.getByText('Are you sure you want to remove this entity ?')).toBeInTheDocument();
  expect(screen.getByLabelText('pim_enrich.entity.attribute.module.mass_delete.modal.label')).toBeInTheDocument();
  expect(screen.getByText('pim_common.delete')).toBeDisabled();
});

test('It allow deletion when user type the text to check', () => {
  const handleConfirm = jest.fn();

  renderWithProviders(
    <DoubleCheckDeleteModal
      title="Entity"
      onCancel={jest.fn()}
      onConfirm={handleConfirm}
      textToCheck="delete"
    >
      Are you sure you want to remove this entity ?
    </DoubleCheckDeleteModal>
  );

  userEvent.type(screen.getByLabelText('pim_enrich.entity.attribute.module.mass_delete.modal.label'), 'delete');
  expect(screen.getByText('pim_common.delete')).toBeEnabled();

  userEvent.click(screen.getByText('pim_common.delete'));
  expect(handleConfirm).toBeCalled();
});