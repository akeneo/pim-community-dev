import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {screen, act, fireEvent} from '@testing-library/react';
import {TableOptionsApp} from '../../../src/attribute/TableOptionsApp';
import {TableConfiguration} from '../../../src/models/TableConfiguration';
jest.mock('../../../src/fetchers/LocaleFetcher');

const tableConfiguration: TableConfiguration = [
  {data_type: 'text', code: 'ingredients', labels: {en_US: 'Ingredients'}, validations: {}},
];

const complexTableConfiguration: TableConfiguration = [
  {data_type: 'select', code: 'ingredients', labels: {en_US: 'Ingredients'}, validations: {}},
  {data_type: 'number', code: 'quantity', labels: {en_US: 'Quantity'}, validations: {}},
  {data_type: 'boolean', code: 'is_allergenic', labels: {en_US: 'Is allergenic'}, validations: {}},
  {data_type: 'text', code: 'part', labels: {en_US: 'For 1 part'}, validations: {}},
];

const waitPageToBeLoaded = async () => {
  expect(await screen.findByText('English (United States)')).toBeInTheDocument();
};

describe('TableOptionsApp', () => {
  it('should render the columns', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableOptionsApp onChange={handleChange} initialTableConfiguration={tableConfiguration} savedColumnCodes={[]} />
    );
    expect(await screen.findByText('English (United States)')).toBeInTheDocument();

    const codeInput = screen.getByLabelText('pim_common.code') as HTMLInputElement;
    const dataTypeInput = screen.getByLabelText('pim_table_attribute.form.attribute.data_type') as HTMLInputElement;
    const english = screen.getByLabelText('English (United States)') as HTMLInputElement;
    const german = screen.getByLabelText('German (Germany)') as HTMLInputElement;
    expect(codeInput.value).toEqual('ingredients');
    expect(dataTypeInput.value).toEqual('pim_table_attribute.properties.data_type.text');
    expect(english.value).toEqual('Ingredients');
    expect(german.value).toEqual('');
  });

  it('should display column information', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableOptionsApp
        onChange={handleChange}
        initialTableConfiguration={complexTableConfiguration}
        savedColumnCodes={[]}
      />
    );
    expect(await screen.findByText('English (United States)')).toBeInTheDocument();

    await act(async () => {
      await fireEvent.click(screen.getAllByRole('row')[1]);
    });

    const codeInput = screen.getByLabelText('pim_common.code') as HTMLInputElement;
    const english = screen.getByLabelText('English (United States)') as HTMLInputElement;
    expect(codeInput.value).toEqual('quantity');
    expect(codeInput).not.toHaveAttribute('readonly');
    expect(english.value).toEqual('Quantity');
  });

  it('should update labels', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableOptionsApp
        onChange={handleChange}
        initialTableConfiguration={tableConfiguration}
        savedColumnCodes={['ingredients']}
      />
    );
    expect(await screen.findByText('English (United States)')).toBeInTheDocument();

    const french = screen.getByLabelText('French (France)') as HTMLInputElement;
    await act(async () => {
      fireEvent.change(french, {target: {value: 'French label'}});
    });

    expect(handleChange).toBeCalledWith([
      {data_type: 'text', code: 'ingredients', labels: {en_US: 'Ingredients', fr_FR: 'French label'}, validations: {}},
    ]);
  });

  it('should drag and drop', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableOptionsApp
        onChange={handleChange}
        initialTableConfiguration={complexTableConfiguration}
        savedColumnCodes={[]}
      />
    );
    expect(await screen.findByText('English (United States)')).toBeInTheDocument();

    await act(async () => {
      await fireEvent.mouseDown(screen.getAllByTestId('dragAndDrop')[1]);
      await fireEvent.dragStart(screen.getAllByRole('row')[1]);
      await fireEvent.dragEnter(screen.getAllByRole('row')[2]);
      await fireEvent.dragLeave(screen.getAllByRole('row')[2]);
      await fireEvent.dragEnter(screen.getAllByRole('row')[3]);
      await fireEvent.drop(screen.getAllByRole('row')[3]);
      await fireEvent.dragEnd(screen.getAllByRole('row')[1]);
    });

    expect(handleChange).toBeCalledWith([
      {data_type: 'select', code: 'ingredients', labels: {en_US: 'Ingredients'}, validations: {}},
      {data_type: 'boolean', code: 'is_allergenic', labels: {en_US: 'Is allergenic'}, validations: {}},
      {data_type: 'text', code: 'part', labels: {en_US: 'For 1 part'}, validations: {}},
      {data_type: 'number', code: 'quantity', labels: {en_US: 'Quantity'}, validations: {}},
    ]);
  });

  it('should render without column', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableOptionsApp onChange={handleChange} initialTableConfiguration={[]} savedColumnCodes={[]} />
    );
    expect(await screen.findByText('pim_table_attribute.form.attribute.empty_title')).toBeInTheDocument();
  });

  it('falls back to the first column when deleting a selected column', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableOptionsApp
        onChange={handleChange}
        initialTableConfiguration={complexTableConfiguration}
        savedColumnCodes={[]}
      />
    );
    await waitPageToBeLoaded();
    act(() => {
      fireEvent.click(screen.getAllByRole('row')[1]);
    });

    const codeInput = screen.getByLabelText('pim_common.code') as HTMLInputElement;
    expect(codeInput.value).toEqual('quantity');
    await act(async () => {
      const deleteButtons = await screen.findAllByTitle('pim_common.delete');
      fireEvent.click(deleteButtons[0]);
    });
    expect(await screen.findByText('pim_table_attribute.form.attribute.confirm_delete')).toBeInTheDocument();

    const deleteButton = await screen.findByText('pim_common.delete');

    const deleteCodeInput = screen.getByLabelText('pim_table_attribute.form.attribute.please_type') as HTMLInputElement;
    act(() => {
      fireEvent.change(deleteCodeInput, {target: {value: 'quantity'}});
    });
    expect(deleteButton).not.toHaveAttribute('disabled');

    act(() => {
      fireEvent.click(deleteButton);
    });
    expect(codeInput.value).toEqual('ingredients');
  });

  it('should set the code field as readonly if the column is saved', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableOptionsApp
        onChange={handleChange}
        initialTableConfiguration={tableConfiguration}
        savedColumnCodes={['ingredients']}
      />
    );
    expect(await screen.findByText('English (United States)')).toBeInTheDocument();
    const codeInput = screen.getByLabelText('pim_common.code') as HTMLInputElement;
    expect(codeInput).toHaveAttribute('readonly');
  });

  it('should render validation fields', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableOptionsApp
        onChange={handleChange}
        initialTableConfiguration={complexTableConfiguration}
        savedColumnCodes={[]}
      />
    );
    expect(await screen.findByText('English (United States)')).toBeInTheDocument();
    act(() => {
      fireEvent.click(screen.getAllByRole('row')[1]);
    });
    const minInput = screen.getByLabelText('pim_table_attribute.validations.min') as HTMLInputElement;
    expect(minInput).toBeInTheDocument();
    const maxInput = screen.getByLabelText('pim_table_attribute.validations.max') as HTMLInputElement;
    expect(maxInput).toBeInTheDocument();
    const decimalsAllowedCheckbox = screen.getByLabelText(
      'pim_table_attribute.validations.decimals_allowed'
    ) as HTMLInputElement;
    expect(decimalsAllowedCheckbox).toBeInTheDocument();

    await act(async () => {
      fireEvent.change(minInput, {target: {value: '10'}});
    });
    expect(handleChange).toHaveBeenCalledWith([
      {code: 'ingredients', data_type: 'select', labels: {en_US: 'Ingredients'}, validations: {}},
      {code: 'quantity', data_type: 'number', labels: {en_US: 'Quantity'}, validations: {min: '10'}},
      {code: 'is_allergenic', data_type: 'boolean', labels: {en_US: 'Is allergenic'}, validations: {}},
      {code: 'part', data_type: 'text', labels: {en_US: 'For 1 part'}, validations: {}},
    ]);

    await act(async () => {
      fireEvent.click(decimalsAllowedCheckbox);
    });
    expect(handleChange).toHaveBeenCalledWith([
      {code: 'ingredients', data_type: 'select', labels: {en_US: 'Ingredients'}, validations: {}},
      {
        code: 'quantity',
        data_type: 'number',
        labels: {en_US: 'Quantity'},
        validations: {min: '10', decimals_allowed: true},
      },
      {code: 'is_allergenic', data_type: 'boolean', labels: {en_US: 'Is allergenic'}, validations: {}},
      {code: 'part', data_type: 'text', labels: {en_US: 'For 1 part'}, validations: {}},
    ]);

    act(() => {
      fireEvent.click(screen.getAllByRole('row')[3]);
    });
    const maxLengthInput = screen.getByLabelText('pim_table_attribute.validations.max_length') as HTMLInputElement;
    expect(maxLengthInput).toBeInTheDocument();
  });
});
