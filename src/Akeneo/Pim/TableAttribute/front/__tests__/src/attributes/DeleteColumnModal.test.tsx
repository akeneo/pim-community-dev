import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {screen, act, fireEvent} from '@testing-library/react';
import {DeleteColumnModal} from '../../../src/attribute/DeleteColumnModal';

describe('DeleteColumnModal', () => {
  it('user cannot access delete button without typing the right column code', async () => {
    const handleClose = jest.fn();
    const handleDelete = jest.fn();
    renderWithProviders(<DeleteColumnModal close={handleClose} columnDefinitionCode={'ingredients'} onDelete={handleDelete} />);
    const deleteButton = await screen.findByText('pim_common.delete');
    expect(deleteButton).toHaveAttribute('disabled');

    const codeInput = screen.getByLabelText('pim_table_attribute.form.attribute.please_type') as HTMLInputElement;

    act(() => {
      fireEvent.change(codeInput, {target: {value: 'ingredients'}});
    });
    expect(deleteButton).not.toHaveAttribute('disabled');

    act(() => {
      fireEvent.click(deleteButton);
    });
    expect(handleClose).toHaveBeenCalledTimes(1);
    expect(handleDelete).toHaveBeenCalledTimes(1);
  });

  it('modal closing when user clicks on cancel button', async () => {
    const handleClose = jest.fn();
    const handleDelete = jest.fn();
    renderWithProviders(<DeleteColumnModal close={handleClose} columnDefinitionCode={'ingredients'} onDelete={handleDelete} />);
    const cancelButton = await screen.findByText('pim_common.cancel');

    act(() => {
      fireEvent.click(cancelButton);
    });
    expect(handleClose).toHaveBeenCalledTimes(1);
    expect(handleDelete).not.toHaveBeenCalled();
  });
});
