import React from 'react';
import {fireEvent, render, screen} from '../../tests/test-utils';
import {NomenclatureValuesDisplayFilter} from '../NomenclatureValuesDisplayFilter';

describe('NomenclatureValuesDisplayFilter', () => {
  it('should render the filter', () => {
    const onChange = jest.fn();
    render(<NomenclatureValuesDisplayFilter filter={'all'} onChange={onChange} />);

    expect(screen.getByText('pim_identifier_generator.nomenclature.display:')).toBeInTheDocument();
    expect(screen.getByText('pim_identifier_generator.nomenclature.filters.all')).toBeInTheDocument();
    fireEvent.click(screen.getByText('pim_identifier_generator.nomenclature.display:'));
    expect(screen.getAllByText('pim_identifier_generator.nomenclature.filters.all')).toHaveLength(2);
    expect(screen.getByText('pim_identifier_generator.nomenclature.filters.error')).toBeInTheDocument();
    expect(screen.getByText('pim_identifier_generator.nomenclature.filters.empty')).toBeInTheDocument();
    expect(screen.getByText('pim_identifier_generator.nomenclature.filters.filled')).toBeInTheDocument();
    fireEvent.click(screen.getByText('pim_identifier_generator.nomenclature.filters.filled'));
    expect(onChange).toBeCalledWith('filled');
  });
});
