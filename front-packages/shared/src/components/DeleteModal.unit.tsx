import React from 'react';
import {fireEvent, screen} from '@testing-library/react';
import {renderWithProviders} from '../tests';
import {DeleteModal} from './DeleteModal';

test('It displays a delete modal', () => {
  renderWithProviders(
    <DeleteModal
      title="Entity"
      onCancel={jest.fn()}
      onConfirm={jest.fn()}
      cancelButtonLabel={'Cancel'}
      confirmButtonLabel={'Confirm'}
    >
      Are you sure you want to remove this entity ?
    </DeleteModal>
  );

  expect(screen.getByText('Are you sure you want to remove this entity ?')).toBeInTheDocument();
  expect(screen.getByText('Entity')).toBeInTheDocument();
  expect(screen.getByText('Cancel')).toBeInTheDocument();
  expect(screen.getByText('Confirm')).toBeInTheDocument();
});

test('It calls on confirm handler when user click on confirm button', () => {
  const handleConfirm = jest.fn();
  renderWithProviders(
    <DeleteModal
      title="Entity"
      onCancel={jest.fn()}
      onConfirm={handleConfirm}
      cancelButtonLabel={'Cancel'}
      confirmButtonLabel={'Confirm'}
    >
      Are you sure you want to remove this entity ?
    </DeleteModal>
  );

  const confirmButton = screen.getByText('Confirm');
  fireEvent.click(confirmButton);

  expect(handleConfirm).toHaveBeenCalled();
});

test('It calls on cancel handler when user click on cancel button', () => {
  const handleCancel = jest.fn();
  renderWithProviders(
    <DeleteModal
      title="Entity"
      onCancel={handleCancel}
      onConfirm={jest.fn()}
      cancelButtonLabel={'Cancel'}
      confirmButtonLabel={'Confirm'}
    >
      Are you sure you want to remove this entity ?
    </DeleteModal>
  );

  const confirmButton = screen.getByText('Cancel');
  fireEvent.click(confirmButton);

  expect(handleCancel).toHaveBeenCalled();
});

test('It does not call on confirm handler when user cannot confirm delete', () => {
  const handleConfirm = jest.fn();
  renderWithProviders(
    <DeleteModal
      title="Entity"
      onCancel={jest.fn()}
      onConfirm={handleConfirm}
      canConfirmDelete={false}
      cancelButtonLabel={'Cancel'}
      confirmButtonLabel={'Confirm'}
    >
      Are you sure you want to remove this entity ?
    </DeleteModal>
  );

  const confirmButton = screen.getByText('Confirm');
  fireEvent.click(confirmButton);

  expect(handleConfirm).not.toHaveBeenCalled();
});
