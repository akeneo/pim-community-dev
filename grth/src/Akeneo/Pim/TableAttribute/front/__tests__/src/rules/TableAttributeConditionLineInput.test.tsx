import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {screen, fireEvent} from '@testing-library/react';
import {TableAttributeConditionLineInput} from '../../../src';
import {getComplexTableAttribute} from '../../factories';
import {mockScroll} from '../../shared/mockScroll';

jest.mock('../../../src/fetchers/SelectOptionsFetcher');
jest.mock('../../../src/fetchers/RecordFetcher');
mockScroll();

describe('TableAttributeConditionLineInput', () => {
  it('should render the component with select options and call changes', async () => {
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

    fireEvent.click((await screen.findAllByTitle('pim_common.open'))[3]);
    expect(await screen.findByText('C')).toBeInTheDocument();
    fireEvent.click(await screen.findByText('C'));

    expect(handleChange).toBeCalledWith({
      operator: 'IN',
      column: 'nutrition_score',
      row: 'pepper',
      value: ['B', 'C'],
    });
  });

  it('should render the component with reference entity and call changes', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableAttributeConditionLineInput
        attribute={getComplexTableAttribute('reference_entity')}
        value={{
          row: 'nantes00e3cffd_f60e_4a51_925b_d2952bd947e1',
          column: 'city',
          operator: 'IN',
          value: ['vannes00bcf56a_2aa9_47c5_ac90_a973460b18a3', 'coueron00893335_2e73_41e3_ac34_763fb6a35107'],
        }}
        onChange={handleChange}
      />
    );

    expect(await screen.findByText('Nantes')).toBeInTheDocument();
    expect(await screen.findByText('City')).toBeInTheDocument();
    expect(await screen.findByText('pim_common.operators.IN')).toBeInTheDocument();
    expect(await screen.findByText('Vannes')).toBeInTheDocument();

    fireEvent.click((await screen.findAllByTitle('pim_common.open'))[3]);
    expect(await screen.findByText('Lannion')).toBeInTheDocument();
    fireEvent.click(await screen.findByText('Lannion'));

    expect(handleChange).toBeCalledWith({
      operator: 'IN',
      column: 'city',
      row: 'nantes00e3cffd_f60e_4a51_925b_d2952bd947e1',
      value: [
        'vannes00bcf56a_2aa9_47c5_ac90_a973460b18a3',
        'coueron00893335_2e73_41e3_ac34_763fb6a35107',
        'lannion00893335_2e73_41e3_ac34_763fb6a35107',
      ],
    });
  });

  it('should not render anything when there is no attribute or no correct values', () => {
    renderWithProviders(
      <TableAttributeConditionLineInput
        attribute={getComplexTableAttribute('reference_entity')}
        value={{
          row: 'nantes00e3cffd_f60e_4a51_925b_d2952bd947e1',
          column: 'city',
          operator: 'IN',
          value: ['vannes00bcf56a_2aa9_47c5_ac90_a973460b18a3', 'coueron00893335_2e73_41e3_ac34_763fb6a35107'],
        }}
        onChange={jest.fn()}
      />
    );

    expect(screen.queryByText('Nantes')).not.toBeInTheDocument();
  });
});
