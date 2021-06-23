import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {screen, fireEvent} from '@testing-library/react';
import {TableInputValue} from '../../../src/product/TableInputValue';
import {getTableValueWithId} from '../factories/TableValue';
import {getComplexTableConfiguration} from '../factories/TableConfiguration';
jest.mock('../../../src/attribute/LocaleLabel');

describe('TableInputValue', () => {
  it('should render the component', () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableInputValue
        valueData={getTableValueWithId()}
        tableConfiguration={getComplexTableConfiguration()}
        searchText={''}
        onChange={handleChange}
      />
    );

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

  it('should callback changes', () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableInputValue
        valueData={getTableValueWithId()}
        tableConfiguration={getComplexTableConfiguration()}
        searchText={''}
        onChange={handleChange}
      />
    );

    fireEvent.change(screen.getByTestId('input-uniqueidsugar-quantity'), {target: {value: '200'}});
    fireEvent.change(screen.getByTestId('input-uniqueidsalt-part'), {target: {value: '42kg'}});
    fireEvent.click(screen.getAllByText('pim_common.yes')[2]);

    expect(handleChange).toBeCalledWith([
      {'unique id': 'uniqueidsugar', ingredient: 'sugar', part: '10g', is_allergenic: true, quantity: '200'},
      {'unique id': 'uniqueidsalt', ingredient: 'salt', part: '42kg', is_allergenic: false},
      {'unique id': 'uniqueidcaramel', ingredient: 'caramel', is_allergenic: true},
    ]);
  });

  it('should search', () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableInputValue
        valueData={getTableValueWithId()}
        tableConfiguration={getComplexTableConfiguration()}
        searchText={'r'}
        onChange={handleChange}
      />
    );

    ['quantity', 'part', 'is_allergenic'].forEach(columnCode => {
      expect(screen.queryByTestId(`input-uniquesalt-${columnCode}`)).not.toBeInTheDocument();
    });
  });
});
