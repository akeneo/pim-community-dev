import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {act, fireEvent, screen} from '@testing-library/react';
import {TableInputValue} from '../../../src/product';
import {getTableValueWithId} from '../factories/TableValue';
import {getComplexTableConfiguration} from '../factories/TableConfiguration';
import {getTableAttribute} from '../factories/Attributes';

jest.mock('../../../src/attribute/LocaleLabel');
jest.mock('../../../src/fetchers/SelectOptionsFetcher');

describe('TableInputValue', () => {
  it('should render the component', async () => {
    renderWithProviders(
      <TableInputValue
        attribute={{...getTableAttribute(), table_configuration: getComplexTableConfiguration()}}
        valueData={getTableValueWithId()}
        searchText={''}
        onChange={jest.fn()}
      />
    );
    expect(await screen.findByText('Sugar')).toBeInTheDocument();

    expect(screen.getByText('Ingredients')).toBeInTheDocument();
    expect(screen.getByText('Quantity')).toBeInTheDocument();
    expect(screen.getByText('For 1 part')).toBeInTheDocument();
    expect(screen.getByText('Is allergenic')).toBeInTheDocument();
    ['quantity', 'part', 'is_allergenic'].forEach(columnCode => {
      ['uniqueidsugar', 'uniqueidsalt', 'uniqueidcaramel'].forEach(uniqueId => {
        expect(screen.getByTestId(`input-${uniqueId}-${columnCode}`)).toBeInTheDocument();
      });
    });
  });

  it('should callback changes', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableInputValue
        attribute={{...getTableAttribute(), table_configuration: getComplexTableConfiguration()}}
        valueData={getTableValueWithId()}
        searchText={''}
        onChange={handleChange}
      />
    );
    expect(await screen.findByText('Sugar')).toBeInTheDocument();

    expect(screen.getAllByText('pim_common.yes')).toHaveLength(1);
    expect(screen.getAllByText('pim_common.no')).toHaveLength(1);
    expect(screen.getAllByTitle('pim_common.open')).toHaveLength(6);

    act(() => {
      fireEvent.click(screen.getAllByTitle('pim_common.clear')[1]); // Clear sugar nutrition score
      fireEvent.change(screen.getByTestId('input-uniqueidsugar-quantity'), {target: {value: '200'}});
      fireEvent.change(screen.getByTestId('input-uniqueidsalt-part'), {target: {value: '42kg'}});
      fireEvent.click(screen.getAllByTitle('pim_common.open')[4]); // Opens the caramel boolean
    });
    fireEvent.click(screen.getAllByText('pim_common.yes')[1]);

    expect(handleChange).toBeCalledWith([
      {...getTableValueWithId()[0], quantity: '200', nutrition_score: undefined},
      {...getTableValueWithId()[1], part: '42kg'},
      {...getTableValueWithId()[2], is_allergenic: true},
    ]);
  });

  it('should search', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableInputValue
        attribute={{...getTableAttribute(), table_configuration: getComplexTableConfiguration()}}
        valueData={getTableValueWithId()}
        searchText={'r'}
        onChange={handleChange}
      />
    );
    expect(await screen.findByText('Sugar')).toBeInTheDocument();

    ['quantity', 'part', 'is_allergenic'].forEach(columnCode => {
      expect(screen.queryByTestId(`input-uniquesalt-${columnCode}`)).not.toBeInTheDocument();
    });
  });

  it('should change inputValue class when a violated cell changed', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableInputValue
        attribute={{...getTableAttribute(), table_configuration: getComplexTableConfiguration()}}
        valueData={getTableValueWithId()}
        onChange={handleChange}
        violatedCells={[
          {
            id: 'uniqueidsugar',
            columnCode: 'quantity',
          },
        ]}
      />
    );
    expect(await screen.findByText('Sugar')).toBeInTheDocument();

    const sugarInput = screen.getByTestId('input-uniqueidsugar-quantity');
    const formerClassList = sugarInput.classList.toString();

    act(() => {
      fireEvent.change(sugarInput, {target: {value: '200'}});
    });
    expect(sugarInput.classList.toString()).not.toEqual(formerClassList);
  });

  it('should delete row', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableInputValue
        attribute={{...getTableAttribute(), table_configuration: getComplexTableConfiguration()}}
        valueData={getTableValueWithId()}
        searchText={''}
        onChange={handleChange}
      />
    );
    expect(await screen.findByText('Sugar')).toBeInTheDocument();

    fireEvent.click(screen.getAllByTitle('pim_common.actions')[1]);
    expect(await screen.findByTitle('pim_table_attribute.form.product.actions.delete_row')).toBeInTheDocument();
    fireEvent.click(screen.getByTitle('pim_table_attribute.form.product.actions.delete_row'));
    expect(handleChange).toBeCalledWith([{...getTableValueWithId()[0]}, {...getTableValueWithId()[2]}]);
  });

  it('should clear row', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableInputValue
        attribute={{...getTableAttribute(), table_configuration: getComplexTableConfiguration()}}
        valueData={getTableValueWithId()}
        searchText={''}
        onChange={handleChange}
      />
    );
    expect(await screen.findByText('Sugar')).toBeInTheDocument();

    fireEvent.click(screen.getAllByTitle('pim_common.actions')[1]);
    expect(await screen.findByTitle('pim_table_attribute.form.product.actions.clear_row')).toBeInTheDocument();
    fireEvent.click(screen.getByTitle('pim_table_attribute.form.product.actions.clear_row'));
    expect(handleChange).toBeCalledWith([
      {...getTableValueWithId()[0]},
      {...getTableValueWithId()[1], part: undefined, is_allergenic: undefined, nutrition_score: undefined},
      {...getTableValueWithId()[2]},
    ]);
  });

  it('should move row to first position', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableInputValue
        attribute={{...getTableAttribute(), table_configuration: getComplexTableConfiguration()}}
        valueData={getTableValueWithId()}
        searchText={''}
        onChange={handleChange}
      />
    );
    expect(await screen.findByText('Sugar')).toBeInTheDocument();

    fireEvent.click(screen.getAllByTitle('pim_common.actions')[1]);
    expect(await screen.findByTitle('pim_table_attribute.form.product.actions.move_first')).toBeInTheDocument();
    fireEvent.click(screen.getByTitle('pim_table_attribute.form.product.actions.move_first'));
    expect(handleChange).toBeCalledWith([
      {...getTableValueWithId()[1]},
      {...getTableValueWithId()[0]},
      {...getTableValueWithId()[2]},
    ]);
  });

  it('should move row to last position', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableInputValue
        attribute={{...getTableAttribute(), table_configuration: getComplexTableConfiguration()}}
        valueData={getTableValueWithId()}
        searchText={''}
        onChange={handleChange}
      />
    );
    expect(await screen.findByText('Sugar')).toBeInTheDocument();

    fireEvent.click(screen.getAllByTitle('pim_common.actions')[1]);
    expect(await screen.findByTitle('pim_table_attribute.form.product.actions.move_last')).toBeInTheDocument();
    fireEvent.click(screen.getByTitle('pim_table_attribute.form.product.actions.move_last'));
    expect(handleChange).toBeCalledWith([
      {...getTableValueWithId()[0]},
      {...getTableValueWithId()[2]},
      {...getTableValueWithId()[1]},
    ]);
  });

  it('should reorder rows', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableInputValue
        attribute={{...getTableAttribute(), table_configuration: getComplexTableConfiguration()}}
        valueData={getTableValueWithId()}
        searchText={''}
        onChange={handleChange}
      />
    );
    expect(await screen.findByText('Sugar')).toBeInTheDocument();

    let dataTransferred = '';
    const dataTransfer = {
      getData: (_format: string) => {
        return dataTransferred;
      },
      setData: (_format: string, data: string) => {
        dataTransferred = data;
      },
    };

    fireEvent.mouseDown(screen.getAllByTestId('dragAndDrop')[1]);
    fireEvent.dragStart(screen.getAllByRole('row')[1], {dataTransfer});
    fireEvent.dragEnter(screen.getAllByRole('row')[2], {dataTransfer});
    fireEvent.dragLeave(screen.getAllByRole('row')[2], {dataTransfer});
    fireEvent.dragEnter(screen.getAllByRole('row')[3], {dataTransfer});
    fireEvent.drop(screen.getAllByRole('row')[3], {dataTransfer});
    fireEvent.dragEnd(screen.getAllByRole('row')[1], {dataTransfer});

    expect(handleChange).toBeCalledWith([
      {...getTableValueWithId()[1]},
      {...getTableValueWithId()[2]},
      {...getTableValueWithId()[0]},
    ]);
  });
});
