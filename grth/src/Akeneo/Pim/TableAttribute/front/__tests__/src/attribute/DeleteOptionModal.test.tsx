import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {act, fireEvent, screen} from '@testing-library/react';
import {DeleteOptionModal} from '../../../src';

describe('DeleteOptionModal', () => {
  it('renders a disabled delete button when the text is not filled', async () => {
    const handleClose = jest.fn();
    const handleDelete = jest.fn();
    renderWithProviders(
      <DeleteOptionModal
        close={handleClose}
        optionCode={'salt'}
        onDelete={handleDelete}
        isFirstColumn={false}
        attributeLabel='Nutrition'
      />
    );
    const deleteButton = await screen.findByText('pim_common.delete');
    expect(deleteButton).toHaveAttribute('disabled');

    const codeInput = screen.getByLabelText('pim_table_attribute.form.attribute.please_type') as HTMLInputElement;

    act(() => {
      fireEvent.change(codeInput, {target: {value: 'salt'}});
    });
    expect(deleteButton).not.toHaveAttribute('disabled');

    act(() => {
      fireEvent.click(deleteButton);
    });
    expect(handleClose).toHaveBeenCalledTimes(1);
    expect(handleDelete).toHaveBeenCalledTimes(1);
  });

  it('closes the modal when user clicks on cancel button', async () => {
    const handleClose = jest.fn();
    const handleDelete = jest.fn();
    renderWithProviders(
      <DeleteOptionModal
        close={handleClose}
        optionCode={'salt'}
        onDelete={handleDelete}
        isFirstColumn={false}
        attributeLabel='Nutrition'
      />
    );
    const cancelButton = await screen.findByText('pim_common.cancel');

    act(() => {
      fireEvent.click(cancelButton);
    });
    expect(handleClose).toHaveBeenCalledTimes(1);
    expect(handleDelete).not.toHaveBeenCalled();
  });
});
