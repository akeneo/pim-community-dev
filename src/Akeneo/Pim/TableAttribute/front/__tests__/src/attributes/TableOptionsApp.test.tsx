import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {screen, act, fireEvent} from '@testing-library/react';
import {TableOptionsApp} from '../../../src/attribute/TableOptionsApp';
import {TableConfiguration} from '../../../src/models/TableConfiguration';
jest.mock('../../../src/fetchers/LocaleFetcher');

const tableConfiguration: TableConfiguration = [
  {data_type: 'text', code: 'ingredients', labels: {en_US: 'Ingredients'}},
];

const complexTableConfiguration: TableConfiguration = [
  {data_type: 'text', code: 'ingredients', labels: {en_US: 'Ingredients'}},
  {data_type: 'text', code: 'quantity', labels: {en_US: 'Quantity'}},
  {data_type: 'text', code: 'aqr', labels: {en_US: 'AQR'}},
  {data_type: 'text', code: 'part', labels: {en_US: 'For 1 part'}},
];

const waitPageToBeLoaded = async () => {
  expect(await screen.findByText('English (United States)')).toBeInTheDocument();
};

describe('TableOptionsApp', () => {
  it('should render the columns', async () => {
    const handleChange = jest.fn();
    renderWithProviders(<TableOptionsApp onChange={handleChange} initialTableConfiguration={tableConfiguration} />);
    expect(await screen.findByText('English (United States)')).toBeInTheDocument();

    const codeInput = screen.getByLabelText('pim_common.code') as HTMLInputElement;
    const dataTypeInput = screen.getByLabelText('pim_table_attribute.form.attribute.data_type') as HTMLInputElement;
    const english = screen.getByLabelText('English (United States)') as HTMLInputElement;
    const german = screen.getByLabelText('German (Germany)') as HTMLInputElement;
    expect(codeInput.value).toEqual('ingredients');
    expect(dataTypeInput.value).toEqual('text');
    expect(english.value).toEqual('Ingredients');
    expect(german.value).toEqual('');
  });

  it('should display column information', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableOptionsApp onChange={handleChange} initialTableConfiguration={complexTableConfiguration} />
    );
    expect(await screen.findByText('English (United States)')).toBeInTheDocument();

    await act(async () => {
      await fireEvent.click(screen.getAllByRole('row')[1]);
    });

    const codeInput = screen.getByLabelText('pim_common.code') as HTMLInputElement;
    const english = screen.getByLabelText('English (United States)') as HTMLInputElement;
    expect(codeInput.value).toEqual('quantity');
    expect(english.value).toEqual('Quantity');
  });

  it('should update labels', async () => {
    const handleChange = jest.fn();
    renderWithProviders(<TableOptionsApp onChange={handleChange} initialTableConfiguration={tableConfiguration} />);
    expect(await screen.findByText('English (United States)')).toBeInTheDocument();

    const french = screen.getByLabelText('French (France)') as HTMLInputElement;
    await act(async () => {
      fireEvent.change(french, {target: {value: 'French label'}});
    });

    expect(handleChange).toBeCalledWith([
      {data_type: 'text', code: 'ingredients', labels: {en_US: 'Ingredients', fr_FR: 'French label'}},
    ]);
  });

  it('should drag and drop', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableOptionsApp onChange={handleChange} initialTableConfiguration={complexTableConfiguration} />
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
      {data_type: 'text', code: 'ingredients', labels: {en_US: 'Ingredients'}},
      {data_type: 'text', code: 'aqr', labels: {en_US: 'AQR'}},
      {data_type: 'text', code: 'part', labels: {en_US: 'For 1 part'}},
      {data_type: 'text', code: 'quantity', labels: {en_US: 'Quantity'}},
    ]);
  });

  it('should render without column', async () => {
    const handleChange = jest.fn();
    renderWithProviders(<TableOptionsApp onChange={handleChange} initialTableConfiguration={[]} />);
    expect(await screen.findByText('pim_table_attribute.form.attribute.empty_title')).toBeInTheDocument();
  });

  it('falls back to the first column when deleting a selected column', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableOptionsApp onChange={handleChange} initialTableConfiguration={complexTableConfiguration} />
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
});
