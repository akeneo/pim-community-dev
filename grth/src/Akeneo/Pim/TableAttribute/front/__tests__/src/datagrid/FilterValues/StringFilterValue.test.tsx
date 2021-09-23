import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {screen, fireEvent} from '@testing-library/react';
import StringFilterValue from "../../../../src/datagrid/FilterValues/StringFilterValue";
import {getComplexTableAttribute} from "../../../factories";

describe('StringFilterValue', () => {
  it('should display current value', () => {
    renderWithProviders(<StringFilterValue
      value={'Lorem ipsum'}
      onChange={jest.fn()}
      columnCode={'description'}
      attribute={getComplexTableAttribute()}
    />);

    expect(screen.getByTitle('Lorem ipsum')).toBeInTheDocument();
  });

  it('should update value', () => {
    const handleChange = jest.fn();
    renderWithProviders(<StringFilterValue
      value={'Lorem ipsum'}
      onChange={handleChange}
      columnCode={'description'}
      attribute={getComplexTableAttribute()}
    />);

    fireEvent.change(screen.getByTitle('Lorem ipsum'), {target: {value: 'Dolor sit amet'}});
    expect(handleChange).toBeCalledWith('Dolor sit amet');
  });
});
