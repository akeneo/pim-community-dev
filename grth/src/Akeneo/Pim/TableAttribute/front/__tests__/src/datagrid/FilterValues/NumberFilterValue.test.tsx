import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {fireEvent, screen} from '@testing-library/react';
import NumberFilterValue from '../../../../src/datagrid/FilterValues/NumberFilterValue';

describe('NumberFilterValue', () => {
  it('should display current value', () => {
    renderWithProviders(<NumberFilterValue value={'69'} onChange={jest.fn()} columnCode={'quantity'} />);

    expect(screen.getByTitle('69')).toBeInTheDocument();
  });

  it('should update value', () => {
    const handleChange = jest.fn();
    renderWithProviders(<NumberFilterValue value={'69'} onChange={handleChange} columnCode={'quantity'} />);

    fireEvent.change(screen.getByTitle('69'), {target: {value: '4000'}});
    expect(handleChange).toBeCalledWith('4000');
  });
});
