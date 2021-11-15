import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {AddColumnModal} from '../../../src/attribute';
import {act, fireEvent, screen} from '@testing-library/react';
import {defaultDataTypesMapping} from '../../factories';

jest.mock('../../../src/attribute/LocaleLabel');

describe('AddColumnModal', () => {
  it('should render the component', () => {
    const handleClose = jest.fn();
    const handleCreate = jest.fn();
    renderWithProviders(
      <AddColumnModal
        close={handleClose}
        onCreate={handleCreate}
        existingColumnCodes={[]}
        dataTypesMapping={defaultDataTypesMapping}
      />
    );

    expect(screen.getByText('pim_table_attribute.form.attribute.add_column')).toBeInTheDocument();
    expect(screen.getByText('pim_common.create')).toBeInTheDocument();
  });

  it('should create default code', () => {
    const handleClose = jest.fn();
    const handleCreate = jest.fn();
    renderWithProviders(
      <AddColumnModal
        close={handleClose}
        onCreate={handleCreate}
        existingColumnCodes={[]}
        dataTypesMapping={defaultDataTypesMapping}
      />
    );

    const codeInput = screen.getByLabelText(/pim_common.code/) as HTMLInputElement;
    const labelInput = screen.getByLabelText('pim_common.label') as HTMLInputElement;

    act(() => {
      fireEvent.change(labelInput, {target: {value: 'This is the label$'}});
    });

    expect(codeInput.value).toEqual('This_is_the_label_');
  });

  it('should not update code once dirty', () => {
    const handleClose = jest.fn();
    const handleCreate = jest.fn();
    renderWithProviders(
      <AddColumnModal
        close={handleClose}
        onCreate={handleCreate}
        existingColumnCodes={[]}
        dataTypesMapping={defaultDataTypesMapping}
      />
    );

    const codeInput = screen.getByLabelText(/pim_common.code/) as HTMLInputElement;
    const labelInput = screen.getByLabelText('pim_common.label') as HTMLInputElement;

    act(() => {
      fireEvent.change(codeInput, {target: {value: 'the_code'}});
    });
    act(() => {
      fireEvent.change(labelInput, {target: {value: 'This is the label$'}});
    });

    expect(codeInput.value).toEqual('the_code');
  });

  it('should add column', async () => {
    const handleClose = jest.fn();
    const handleCreate = jest.fn();
    renderWithProviders(
      <AddColumnModal
        close={handleClose}
        onCreate={handleCreate}
        existingColumnCodes={['quantity']}
        dataTypesMapping={defaultDataTypesMapping}
      />
    );

    const codeInput = screen.getByLabelText(/pim_common.code/) as HTMLInputElement;
    const labelInput = screen.getByLabelText('pim_common.label') as HTMLInputElement;
    const createButton = screen.getByText('pim_common.create') as HTMLButtonElement;

    fireEvent.change(labelInput, {target: {value: 'Ingredients'}});
    fireEvent.change(codeInput, {target: {value: 'ingredient'}});
    fireEvent.click(screen.getByTitle('pim_common.open'));
    expect(await screen.findByText('pim_table_attribute.properties.data_type.text')).toBeInTheDocument();
    fireEvent.click(screen.getByText('pim_table_attribute.properties.data_type.text'));

    expect(createButton.disabled).toEqual(false);

    act(() => {
      fireEvent.click(createButton);
    });

    expect(handleCreate).toHaveBeenCalledWith({
      code: 'ingredient',
      data_type: 'text',
      labels: {
        en_US: 'Ingredients',
      },
      validations: {},
    });
    expect(handleClose).toHaveBeenCalled();
  });

  it('should restrict the data types for the first column', async () => {
    const handleClose = jest.fn();
    const handleCreate = jest.fn();
    renderWithProviders(
      <AddColumnModal
        close={handleClose}
        onCreate={handleCreate}
        existingColumnCodes={[]}
        dataTypesMapping={defaultDataTypesMapping}
      />
    );

    expect(screen.getByText('pim_table_attribute.form.attribute.first_column_type_helper')).toBeInTheDocument();
    fireEvent.click(screen.getByTitle('pim_common.open'));

    expect(await screen.findByText('pim_table_attribute.properties.data_type.select')).toBeInTheDocument();
    expect(screen.queryByText('pim_table_attribute.properties.data_type.text')).toBeNull();
    expect(screen.queryByText('pim_table_attribute.properties.data_type.number')).toBeNull();
    expect(screen.queryByText('pim_table_attribute.properties.data_type.boolean')).toBeNull();
  });

  it('should display validation errors', async () => {
    const handleClose = jest.fn();
    const handleCreate = jest.fn();
    renderWithProviders(
      <AddColumnModal
        close={handleClose}
        onCreate={handleCreate}
        existingColumnCodes={['quantity']}
        dataTypesMapping={defaultDataTypesMapping}
      />
    );

    expect(screen.queryByText('pim_table_attribute.form.attribute.first_column_type_helper')).toBeNull();
    const codeInput = screen.getByLabelText(/pim_common.code/) as HTMLInputElement;
    const createButton = screen.getByText('pim_common.create') as HTMLButtonElement;

    fireEvent.click(screen.getByTitle('pim_common.open'));
    expect(await screen.findByText('pim_table_attribute.properties.data_type.text')).toBeInTheDocument();
    fireEvent.click(screen.getByText('pim_table_attribute.properties.data_type.text'));

    fireEvent.change(codeInput, {target: {value: 'a wrong code'}});
    expect(screen.getByText('pim_table_attribute.validations.invalid_column_code')).toBeInTheDocument();
    expect(createButton.disabled).toEqual(true);

    fireEvent.change(codeInput, {target: {value: ''}});
    expect(screen.getByText('pim_table_attribute.validations.column_code_must_be_filled')).toBeInTheDocument();
    expect(createButton.disabled).toEqual(true);

    fireEvent.change(codeInput, {target: {value: 'quantity'}});
    expect(screen.getByText('pim_table_attribute.validations.duplicated_column_code')).toBeInTheDocument();
    expect(createButton.disabled).toEqual(true);

    fireEvent.change(codeInput, {target: {value: 'product'}});
    expect(screen.getByText('pim_table_attribute.validations.not_available_code')).toBeInTheDocument();
    expect(createButton.disabled).toEqual(true);

    fireEvent.change(codeInput, {target: {value: 'PRODUCT_MODel'}});
    expect(screen.getByText('pim_table_attribute.validations.not_available_code')).toBeInTheDocument();
    expect(createButton.disabled).toEqual(true);

    fireEvent.change(codeInput, {target: {value: 'attribute'}});
    expect(screen.getByText('pim_table_attribute.validations.not_available_code')).toBeInTheDocument();
    expect(createButton.disabled).toEqual(true);

    fireEvent.change(codeInput, {target: {value: 'a_good_code'}});
    expect(createButton.disabled).toEqual(false);
  });
});
