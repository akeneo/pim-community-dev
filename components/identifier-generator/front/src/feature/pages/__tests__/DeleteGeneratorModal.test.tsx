import React from 'react';
import userEvent from '@testing-library/user-event';
import {DeleteIdentifierGeneratorModal} from '../DeleteGeneratorModal';
import {render, screen, waitFor} from '../../tests/test-utils';
import {fireEvent} from '@testing-library/react';

jest.mock('@akeneo-pim-community/connectivity-connection/src/shared/notify');

describe('DeleteIdentifierGeneratorModal', () => {
  const closeModal = jest.fn();
  const deleteGenerator = jest.fn();
  const notify = jest.fn();

  describe('ConfirmButton', () => {
    let confirmButton: HTMLElement;
    let generatorCodeInput: Node | Window;

    beforeEach(() => {
      render(
        <DeleteIdentifierGeneratorModal
          generatorCode="this_code"
          closeModal={closeModal}
          deleteGenerator={deleteGenerator}
        />
      );
      confirmButton = screen.getByText('pim_common.delete');
      generatorCodeInput = screen.getByRole('textbox', {name: 'pim_identifier_generator.deletion.type'});
    });

    it('should enabled confirm button if code is valid', () => {
      expect(confirmButton).toBeDisabled();

      fireEvent.change(generatorCodeInput, {target: {value: 'this_code'}});
      expect(generatorCodeInput).toHaveValue('this_code');
      expect(confirmButton).toBeEnabled();
    });
    it('should disabled confirm button if code is invalid', () => {
      expect(confirmButton).toBeDisabled();

      fireEvent.change(generatorCodeInput, {target: {value: 'unknown_code'}});
      expect(generatorCodeInput).toHaveValue('unknown_code');
      expect(confirmButton).toBeDisabled();
    });
  });

  describe('closeButton', () => {
    it('should called closeModal function', () => {
      render(
        <DeleteIdentifierGeneratorModal
          generatorCode="this_code"
          closeModal={closeModal}
          deleteGenerator={deleteGenerator}
        />
      );

      userEvent.click(screen.getByText('pim_common.cancel'));
      expect(closeModal).toHaveBeenCalledTimes(1);
    });
  });
});
