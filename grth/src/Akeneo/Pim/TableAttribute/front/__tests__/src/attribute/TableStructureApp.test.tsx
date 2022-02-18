import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {act, screen} from '@testing-library/react';
import {TableStructureApp} from '../../../src';
import {getComplexTableAttribute, getComplexTableConfiguration, getSimpleTableConfiguration} from '../../factories';
import {fireEvent} from '@testing-library/dom';
import {dragAndDrop} from '../../shared/dragAndDrop';

jest.mock('../../../src/fetchers/LocaleFetcher');
jest.mock('../../../src/attribute/AddColumnModal');

const waitPageToBeLoaded = async () => {
  expect(await screen.findByText('English (United States)')).toBeInTheDocument();
};

describe('TableStructureApp', () => {
  it('should render the columns', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableStructureApp
        onChange={handleChange}
        initialTableConfiguration={getSimpleTableConfiguration()}
        savedColumnCodes={[]}
        attribute={getComplexTableAttribute()}
      />
    );
    await waitPageToBeLoaded();

    const codeInput = screen.getByLabelText(/pim_table_attribute.form.attribute.column_code/) as HTMLInputElement;
    const dataTypeInput = screen.getByLabelText(/pim_table_attribute.form.attribute.data_type/) as HTMLInputElement;
    const english = screen.getByLabelText('English (United States)') as HTMLInputElement;
    const german = screen.getByLabelText('German (Germany)') as HTMLInputElement;
    expect(codeInput.value).toEqual('ingredient');
    expect(dataTypeInput.value).toEqual('pim_table_attribute.properties.data_type.select');
    expect(english.value).toEqual('Ingredients');
    expect(german.value).toEqual('');
  });

  it('should display column information', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableStructureApp
        onChange={handleChange}
        initialTableConfiguration={getComplexTableConfiguration()}
        savedColumnCodes={[]}
        attribute={getComplexTableAttribute()}
      />
    );
    await waitPageToBeLoaded();

    act(() => {
      fireEvent.click(screen.getAllByRole('row')[1]);
    });

    const codeInput = screen.getByLabelText(/pim_table_attribute.form.attribute.column_code/) as HTMLInputElement;
    const english = screen.getByLabelText('English (United States)') as HTMLInputElement;
    expect(codeInput.value).toEqual('quantity');
    expect(codeInput).not.toHaveAttribute('readonly');
    expect(english.value).toEqual('Quantity');

    act(() => {
      fireEvent.click(screen.getAllByRole('row')[0]);
    });

    expect(codeInput.value).toEqual('ingredient');
  });

  it('should update labels', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableStructureApp
        onChange={handleChange}
        initialTableConfiguration={getSimpleTableConfiguration()}
        savedColumnCodes={['ingredient']}
        attribute={getComplexTableAttribute()}
      />
    );
    await waitPageToBeLoaded();

    const french = screen.getByLabelText('French (France)') as HTMLInputElement;
    await act(async () => {
      fireEvent.change(french, {target: {value: 'French label'}});
    });

    expect(handleChange).toBeCalledWith([
      {
        data_type: 'select',
        code: 'ingredient',
        labels: {en_US: 'Ingredients', fr_FR: 'French label'},
        validations: {},
      },
    ]);
  });

  it('should drag and drop', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableStructureApp
        onChange={handleChange}
        initialTableConfiguration={getComplexTableConfiguration()}
        savedColumnCodes={[]}
        attribute={getComplexTableAttribute()}
      />
    );
    await waitPageToBeLoaded();

    // Move 2nd column to 4th place
    dragAndDrop(0, 3);

    expect(handleChange).toBeCalledWith([
      getComplexTableConfiguration()[0],
      getComplexTableConfiguration()[2],
      getComplexTableConfiguration()[3],
      getComplexTableConfiguration()[1],
      getComplexTableConfiguration()[4],
      getComplexTableConfiguration()[5],
    ]);
  });

  it('should render without column', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableStructureApp
        onChange={handleChange}
        initialTableConfiguration={[]}
        savedColumnCodes={[]}
        attribute={getComplexTableAttribute()}
      />
    );
    expect(await screen.findByText('pim_table_attribute.form.attribute.empty_title')).toBeInTheDocument();
  });

  it('falls back to the first column when deleting a selected column', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableStructureApp
        onChange={handleChange}
        initialTableConfiguration={getComplexTableConfiguration()}
        savedColumnCodes={[]}
        attribute={getComplexTableAttribute()}
      />
    );
    await waitPageToBeLoaded();

    act(() => {
      fireEvent.click(screen.getAllByRole('row')[1]);
    });

    const codeInput = screen.getByLabelText(/pim_table_attribute.form.attribute.column_code/) as HTMLInputElement;
    expect(codeInput.value).toEqual('quantity');
    await act(async () => {
      const deleteButtons = await screen.findAllByTitle('pim_common.delete');
      fireEvent.click(deleteButtons[0]);
    });
    expect(await screen.findByText('pim_table_attribute.form.attribute.confirm_column_delete')).toBeInTheDocument();

    const deleteButton = await screen.findByText('pim_common.delete');

    const deleteCodeInput = screen.getByLabelText('pim_table_attribute.form.attribute.please_type') as HTMLInputElement;
    act(() => {
      fireEvent.change(deleteCodeInput, {target: {value: 'quantity'}});
    });
    expect(deleteButton).not.toHaveAttribute('disabled');

    act(() => {
      fireEvent.click(deleteButton);
    });
    expect(codeInput.value).toEqual('ingredient');
  });

  it('should set the code field as readonly if the column is saved', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableStructureApp
        onChange={handleChange}
        initialTableConfiguration={getSimpleTableConfiguration()}
        savedColumnCodes={['ingredient']}
        attribute={getComplexTableAttribute()}
      />
    );
    await waitPageToBeLoaded();

    const codeInput = screen.getByLabelText(/pim_table_attribute.form.attribute.column_code/) as HTMLInputElement;
    expect(codeInput).toHaveAttribute('readonly');
  });

  it('should render validation fields', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableStructureApp
        onChange={handleChange}
        initialTableConfiguration={getComplexTableConfiguration()}
        savedColumnCodes={[]}
        attribute={getComplexTableAttribute()}
      />
    );
    await waitPageToBeLoaded();

    act(() => {
      fireEvent.click(screen.getAllByRole('row')[1]);
    });
    expect(screen.getByLabelText('pim_table_attribute.validations.min')).toBeInTheDocument();
    expect(screen.getByLabelText('pim_table_attribute.validations.max')).toBeInTheDocument();
    expect(screen.getByLabelText('pim_table_attribute.validations.decimals_allowed')).toBeInTheDocument();

    act(() => {
      fireEvent.click(screen.getAllByRole('row')[3]);
    });
    const maxLengthInput = screen.getByLabelText('pim_table_attribute.validations.max_length') as HTMLInputElement;
    expect(maxLengthInput).toBeInTheDocument();
    fireEvent.change(maxLengthInput, {target: {value: 50}});

    expect(handleChange).toHaveBeenCalledWith([
      getComplexTableConfiguration()[0],
      getComplexTableConfiguration()[1],
      getComplexTableConfiguration()[2],
      {...getComplexTableConfiguration()[3], validations: {max_length: 50}},
      getComplexTableConfiguration()[4],
      getComplexTableConfiguration()[5],
    ]);
  });

  it('should add a column', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableStructureApp
        onChange={handleChange}
        initialTableConfiguration={getSimpleTableConfiguration()}
        savedColumnCodes={[]}
        attribute={getComplexTableAttribute()}
      />
    );
    await waitPageToBeLoaded();

    act(() => {
      fireEvent.click(screen.getByText('pim_table_attribute.form.attribute.add_column'));
    });
    act(() => {
      fireEvent.click(screen.getByText('Mock create'));
    });
    expect(handleChange).toBeCalledWith([
      {data_type: 'select', code: 'ingredient', labels: {en_US: 'Ingredients'}, validations: {}},
      {data_type: 'text', code: 'new_column', labels: {en_US: 'New column'}, validations: {}},
    ]);
  });
});
