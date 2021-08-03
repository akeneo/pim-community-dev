import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {screen, fireEvent, act} from '@testing-library/react';
import {TableInputValue} from '../../../src/product/TableInputValue';
import {getTableValueWithId} from '../factories/TableValue';
import {getComplexTableConfiguration} from '../factories/TableConfiguration';
import {getTableAttribute} from '../factories/Attributes';
jest.mock('../../../src/attribute/LocaleLabel');
jest.mock('../../../src/fetchers/SelectOptionsFetcher');

describe('TableInputValue', () => {
  it('should render the component', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableInputValue
        attribute={getTableAttribute()}
        valueData={getTableValueWithId()}
        tableConfiguration={getComplexTableConfiguration()}
        searchText={''}
        onChange={handleChange}
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
        attribute={getTableAttribute()}
        valueData={getTableValueWithId()}
        tableConfiguration={getComplexTableConfiguration()}
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
        attribute={getTableAttribute()}
        valueData={getTableValueWithId()}
        tableConfiguration={getComplexTableConfiguration()}
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
        attributeCode={'nutrition'}
        valueData={getTableValueWithId()}
        tableConfiguration={getComplexTableConfiguration()}
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
});
