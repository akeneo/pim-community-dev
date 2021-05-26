import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {AddColumnModal} from '../../../src/attribute/AddColumnModal';
import {act, screen, fireEvent} from '@testing-library/react';
import {Locale} from '@akeneo-pim-community/shared';

beforeAll(() =>
  global.fetch.mockImplementation(async (url?: string | Request) => {
    if (url === 'pim_enrich_locale_rest_index') {
      const locales: Locale[] = [
        {
          code: 'en_US',
          label: 'English (United States)',
          region: 'United States',
          language: 'English',
        },
        {
          code: 'fr_FR',
          label: 'French (France)',
          region: 'France',
          language: 'French',
        },
      ];
      return new Response(JSON.stringify(locales));
    }

    throw new Error(`Unknown route: "${url}"`);
  })
);

describe('AddColumnModal', () => {
  it('should render the component', async () => {
    const handleClose = jest.fn();
    const handleCreate = jest.fn();
    renderWithProviders(<AddColumnModal close={handleClose} onCreate={handleCreate} existingColumnCodes={[]} />);
    expect(await screen.findByText('English')).toBeInTheDocument();

    expect(screen.getByText('pim_table_attribute.form.attribute.add_column')).toBeInTheDocument();
    expect(screen.getByText('pim_common.create')).toBeInTheDocument();
  });

  it('should create default code', async () => {
    const handleClose = jest.fn();
    const handleCreate = jest.fn();
    renderWithProviders(<AddColumnModal close={handleClose} onCreate={handleCreate} existingColumnCodes={[]} />);
    expect(await screen.findByText('English')).toBeInTheDocument();

    const codeInput = screen.getByLabelText('pim_common.code') as HTMLInputElement;
    const labelInput = screen.getByLabelText('pim_common.label') as HTMLInputElement;

    await act(async () => {
      fireEvent.change(labelInput, {target: {value: 'This is the label$'}});
    });

    expect(codeInput.value).toEqual('This_is_the_label_');
  });

  it('should not update code once dirty', async () => {
    const handleClose = jest.fn();
    const handleCreate = jest.fn();
    renderWithProviders(<AddColumnModal close={handleClose} onCreate={handleCreate} existingColumnCodes={[]} />);
    expect(await screen.findByText('English')).toBeInTheDocument();

    const codeInput = screen.getByLabelText('pim_common.code') as HTMLInputElement;
    const labelInput = screen.getByLabelText('pim_common.label') as HTMLInputElement;

    await act(async () => {
      fireEvent.change(codeInput, {target: {value: 'the_code'}});
    });
    await act(async () => {
      fireEvent.change(labelInput, {target: {value: 'This is the label$'}});
    });

    expect(codeInput.value).toEqual('the_code');
  });

  it('should add column', async () => {
    const handleClose = jest.fn();
    const handleCreate = jest.fn();
    renderWithProviders(<AddColumnModal close={handleClose} onCreate={handleCreate} existingColumnCodes={[]} />);
    expect(await screen.findByText('English')).toBeInTheDocument();

    const codeInput = screen.getByLabelText('pim_common.code') as HTMLInputElement;
    const labelInput = screen.getByLabelText('pim_common.label') as HTMLInputElement;
    const dataTypeInput = screen.getByLabelText('pim_table_attribute.form.attribute.data_type') as HTMLInputElement;
    const createButton = screen.getByText('pim_common.create') as HTMLButtonElement;

    fireEvent.change(labelInput, {target: {value: 'Ingredients'}});
    fireEvent.change(codeInput, {target: {value: 'ingredients'}});
    fireEvent.focus(dataTypeInput);
    expect(screen.getByText('pim_table_attribute.properties.data_type.text')).toBeInTheDocument();
    fireEvent.click(screen.getByText('pim_table_attribute.properties.data_type.text'));

    expect(createButton.disabled).toEqual(false);

    await act(async () => {
      fireEvent.click(createButton);
    });

    expect(handleCreate).toHaveBeenCalledWith({
      code: 'ingredients',
      data_type: 'text',
      labels: {
        en_US: 'Ingredients',
      },
    });
    expect(handleClose).toHaveBeenCalled();
  });

  it('should display validation errors', async () => {
    const handleClose = jest.fn();
    const handleCreate = jest.fn();
    renderWithProviders(
      <AddColumnModal close={handleClose} onCreate={handleCreate} existingColumnCodes={['quantity']} />
    );
    expect(await screen.findByText('English')).toBeInTheDocument();
    const codeInput = screen.getByLabelText('pim_common.code') as HTMLInputElement;
    const dataTypeInput = screen.getByLabelText('pim_table_attribute.form.attribute.data_type') as HTMLInputElement;
    const createButton = screen.getByText('pim_common.create') as HTMLButtonElement;

    fireEvent.focus(dataTypeInput);
    expect(screen.getByText('pim_table_attribute.properties.data_type.text')).toBeInTheDocument();
    fireEvent.click(screen.getByText('pim_table_attribute.properties.data_type.text'));

    fireEvent.change(codeInput, {target: {value: 'a wrong code'}});
    expect(screen.getByText('pim_table_attribute.validations.invalid_code')).toBeInTheDocument();
    expect(createButton.disabled).toEqual(true);

    fireEvent.change(codeInput, {target: {value: ''}});
    expect(screen.getByText('pim_table_attribute.validations.column_code_must_be_filled')).toBeInTheDocument();
    expect(createButton.disabled).toEqual(true);

    fireEvent.change(codeInput, {target: {value: 'quantity'}});
    expect(screen.getByText('pim_table_attribute.validations.duplicated_column_code')).toBeInTheDocument();
    expect(createButton.disabled).toEqual(true);

    fireEvent.change(codeInput, {target: {value: 'a_good_code'}});
    expect(createButton.disabled).toEqual(false);
  });
});
