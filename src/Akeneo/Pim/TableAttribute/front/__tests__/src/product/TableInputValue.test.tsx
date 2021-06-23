import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {screen, fireEvent} from '@testing-library/react';
import { TableConfiguration } from "../../../src/models/TableConfiguration";
import { TableInputValue } from "../../../src/product/TableInputValue";
import { TableValueWithId } from "../../../src/product/TableFieldApp";
jest.mock('../../../src/attribute/LocaleLabel');

const tableConfiguration: TableConfiguration = [
  { code: 'ingredient', labels: { 'en_US': 'Ingredients' }, validations: {}, data_type: 'select' },
  { code: 'quantity', labels: {}, validations: {}, data_type: 'number' },
  { code: 'aqr', labels: {}, validations: {}, data_type: 'text' },
  { code: 'is_allergenic', labels: {}, validations: {}, data_type: 'boolean' },
];

const value: TableValueWithId = [
  { 'unique id': 'a', ingredient: 'sugar', quantity: 100, aqr: 'Not good', is_allergenic: true },
  { 'unique id': 'b', ingredient: 'salt', aqr: 'Good but not too much', is_allergenic: false },
  { 'unique id': 'c', ingredient: 'sugar' },
];

describe('TableInputValue', () => {
  it('should render the component', () => {
    const handleChange = jest.fn();
    renderWithProviders(<TableInputValue
      valueData={value}
      tableConfiguration={tableConfiguration}
      searchText={''}
      onChange={handleChange}
    />);

    expect(screen.getByText('Ingredients')).toBeInTheDocument();
    expect(screen.getByText('[quantity]')).toBeInTheDocument();
    expect(screen.getByText('[aqr]')).toBeInTheDocument();
    expect(screen.getByText('[is_allergenic]')).toBeInTheDocument();
    ['quantity', 'aqr', 'is_allergenic'].forEach(columnCode => {
      ['a', 'b', 'c'].forEach(uniqueId => {
        expect(screen.getByTestId(`input-${uniqueId}-${columnCode}`)).toBeInTheDocument();
      });
    });
  });

  it('should callback changes', () => {
    const handleChange = jest.fn();
    renderWithProviders(<TableInputValue
      valueData={value}
      tableConfiguration={tableConfiguration}
      searchText={''}
      onChange={handleChange}
    />);

    fireEvent.change(screen.getByTestId('input-a-quantity'), {target: {value: '200'}});
    fireEvent.change(screen.getByTestId('input-b-aqr'), {target: {value: 'This is a new AQR'}});
    fireEvent.click(screen.getAllByText('pim_common.yes')[2]);

    expect(handleChange).toBeCalledWith([
      {'aqr': 'Not good', 'ingredient': 'sugar', 'is_allergenic': true, 'quantity': '200', 'unique id': 'a'},
      {'aqr': 'This is a new AQR', 'ingredient': 'salt', 'is_allergenic': false, 'unique id': 'b'},
      {'ingredient': 'sugar', 'unique id': 'c', 'is_allergenic': true}
    ]);
  });

  it('should search', () => {
    const handleChange = jest.fn();
    renderWithProviders(<TableInputValue
      valueData={value}
      tableConfiguration={tableConfiguration}
      searchText={'r'}
      onChange={handleChange}
    />);

    ['quantity', 'aqr', 'is_allergenic'].forEach(columnCode => {
      expect(screen.queryByTestId(`input-b-${columnCode}`)).not.toBeInTheDocument();
    });
  });
});
