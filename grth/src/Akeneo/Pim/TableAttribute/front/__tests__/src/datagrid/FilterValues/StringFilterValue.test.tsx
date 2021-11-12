import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {fireEvent, screen} from '@testing-library/react';
import StringFilterValue from '../../../../src/datagrid/FilterValues/StringFilterValue';

describe('StringFilterValue', () => {
  it('should display current value', () => {
    renderWithProviders(<StringFilterValue value={'Lorem ipsum'} onChange={jest.fn()} columnCode={'description'} />);

    expect(screen.getByTitle('Lorem ipsum')).toBeInTheDocument();
  });

  it('should update value', () => {
    const handleChange = jest.fn();
    renderWithProviders(<StringFilterValue value={'Lorem ipsum'} onChange={handleChange} columnCode={'description'} />);

    fireEvent.change(screen.getByTitle('Lorem ipsum'), {target: {value: 'Dolor sit amet'}});
    expect(handleChange).toBeCalledWith('Dolor sit amet');
  });
});
