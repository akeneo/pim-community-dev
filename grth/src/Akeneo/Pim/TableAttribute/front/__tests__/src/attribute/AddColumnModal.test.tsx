import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {AddColumnModal} from '../../../src';
import {act, fireEvent, screen} from '@testing-library/react';
import {renderWithFeatureFlag} from '../../shared/renderWithFeatureFlag';

jest.mock('../../../src/attribute/LocaleLabel');
jest.mock('../../../src/fetchers/ReferenceEntityFetcher');
jest.mock('../../../src/fetchers/MeasurementFamilyFetcher');

describe('AddColumnModal', () => {
  it('should render the component', () => {
    const handleClose = jest.fn();
    const handleCreate = jest.fn();
    renderWithProviders(<AddColumnModal close={handleClose} onCreate={handleCreate} existingColumnCodes={[]} />);

    expect(screen.getByText('pim_table_attribute.form.attribute.add_column')).toBeInTheDocument();
    expect(screen.getByText('pim_common.create')).toBeInTheDocument();
  });

  it('should create default code', () => {
    const handleClose = jest.fn();
    const handleCreate = jest.fn();
    renderWithProviders(<AddColumnModal close={handleClose} onCreate={handleCreate} existingColumnCodes={[]} />);

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
    renderWithProviders(<AddColumnModal close={handleClose} onCreate={handleCreate} existingColumnCodes={[]} />);

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
      <AddColumnModal close={handleClose} onCreate={handleCreate} existingColumnCodes={['quantity']} />
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
    renderWithProviders(<AddColumnModal close={jest.fn()} onCreate={jest.fn()} existingColumnCodes={[]} />);

    expect(screen.getByText('pim_table_attribute.form.attribute.first_column_type_helper')).toBeInTheDocument();
    fireEvent.click(screen.getByTitle('pim_common.open'));

    expect(await screen.findByText('pim_table_attribute.properties.data_type.select')).toBeInTheDocument();
    expect(screen.queryByText('pim_table_attribute.properties.data_type.text')).toBeNull();
    expect(screen.queryByText('pim_table_attribute.properties.data_type.number')).toBeNull();
    expect(screen.queryByText('pim_table_attribute.properties.data_type.boolean')).toBeNull();
  });

  it('should select reference entity', async () => {
    const handleCreate = jest.fn();
    renderWithFeatureFlag(<AddColumnModal close={jest.fn()} onCreate={handleCreate} existingColumnCodes={[]} />, {
      reference_entity: true,
    });

    fireEvent.change(screen.getByLabelText(/pim_common.label/), {target: {value: 'Reference entity column'}});
    expect(
      screen.getByText('pim_table_attribute.form.attribute.first_column_type_helper_with_reference_entity')
    ).toBeInTheDocument();
    fireEvent.click(screen.getByTitle('pim_common.open'));
    fireEvent.click(await screen.findByText('pim_table_attribute.properties.data_type.reference_entity'));
    expect(screen.getByText('pim_common.create') as HTMLButtonElement).toBeDisabled();
    expect(screen.getByText('pim_table_attribute.form.attribute.reference_entity')).toBeInTheDocument();
    fireEvent.click(screen.getAllByTitle('pim_common.open')[1]);
    expect(await screen.findByText('City')).toBeInTheDocument();
    fireEvent.click(screen.getByText('City'));
    expect(screen.getByText('pim_common.create') as HTMLButtonElement).toBeEnabled();
    fireEvent.click(screen.getByText('pim_common.create'));
    expect(handleCreate).toBeCalledWith({
      code: 'Reference_entity_column',
      labels: {en_US: 'Reference entity column'},
      data_type: 'reference_entity',
      reference_entity_identifier: 'city',
      validations: {},
    });
  });

  it('should display validation errors', async () => {
    const handleClose = jest.fn();
    const handleCreate = jest.fn();
    renderWithProviders(
      <AddColumnModal close={handleClose} onCreate={handleCreate} existingColumnCodes={['quantity']} />
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

  it('should select measurement family and measurement unit', async () => {
    const handleCreate = jest.fn();
    renderWithProviders(
      <AddColumnModal close={jest.fn()} onCreate={handleCreate} existingColumnCodes={['firstColumnCode']} />
    );

    fireEvent.change(screen.getByLabelText(/pim_common.label/), {target: {value: 'Measurement column'}});
    fireEvent.click(screen.getByTitle('pim_common.open'));
    fireEvent.click(await screen.findByText('pim_table_attribute.properties.data_type.measurement'));
    expect(screen.getByText('pim_common.create') as HTMLButtonElement).toBeDisabled();

    fireEvent.click(screen.getAllByTitle('pim_common.open')[1]);
    fireEvent.click(await screen.findByText('Electric charge'));
    expect(screen.getByText('pim_common.create') as HTMLButtonElement).toBeDisabled();

    fireEvent.click(screen.getAllByTitle('pim_common.open')[2]);
    fireEvent.click(screen.getByText('Millicoulomb'));
    expect(screen.getByText('pim_common.create') as HTMLButtonElement).toBeEnabled();

    fireEvent.click(screen.getByText('pim_common.create'));

    expect(handleCreate).toBeCalledWith({
      code: 'Measurement_column',
      labels: {
        en_US: 'Measurement column',
      },
      validations: {},
      data_type: 'measurement',
      measurement_family_code: 'ElectricCharge',
      measurement_default_unit_code: 'MILLICOULOMB',
    });
  });
});
