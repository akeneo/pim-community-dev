import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {screen, fireEvent} from '@testing-library/react';
import {TableAttributeConditionLineInput} from '../../../src';
import {getComplexTableAttribute} from '../../factories';
import {mockScroll} from "../../shared/mockScroll";

jest.mock('../../../src/fetchers/SelectOptionsFetcher');
mockScroll();

describe('TableAttributeConditionLineInput', () => {
  it('should render the before component and call changes', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableAttributeConditionLineInput
        attribute={getComplexTableAttribute()}
        value={{
          operator: 'IN',
          column: 'nutrition_score',
          row: 'pepper',
          value: ['B'],
        }}
        onChange={handleChange}
      />
    );

    expect(await screen.findByText('Pepper')).toBeInTheDocument();
    expect(await screen.findByText('Nutrition score')).toBeInTheDocument();
    expect(await screen.findByText('pim_common.operators.IN')).toBeInTheDocument();
    expect(await screen.findByText('B')).toBeInTheDocument();

    fireEvent.click(screen.getAllByTitle('pim_common.open')[3]);
    expect(await screen.findByText('C')).toBeInTheDocument();
    fireEvent.click(screen.getByText('C'));

    expect(handleChange).toBeCalledWith({
      operator: 'IN',
      column: 'nutrition_score',
      row: 'pepper',
      value: ['B', 'C'],
    });
  });
});
