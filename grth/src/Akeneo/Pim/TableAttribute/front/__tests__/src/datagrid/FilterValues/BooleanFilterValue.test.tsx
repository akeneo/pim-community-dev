import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {act, screen, fireEvent} from '@testing-library/react';
import BooleanFilterValue from '../../../../src/datagrid/FilterValues/BooleanFilterValue';
import {getComplexTableAttribute} from '../../../factories';

describe('BooleanFilterValue', () => {
  it('should display current value', () => {
    renderWithProviders(
      <BooleanFilterValue
        value={true}
        onChange={jest.fn()}
        columnCode={'is_allergenic'}
        attribute={getComplexTableAttribute()}
      />
    );

    expect(screen.getByText('pim_common.yes')).toBeInTheDocument();
  });

  it('should update value', () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <BooleanFilterValue
        value={false}
        onChange={handleChange}
        columnCode={'is_allergenic'}
        attribute={getComplexTableAttribute()}
      />
    );

    act(() => {
      fireEvent.click(screen.getByTitle('pim_common.open'));
    });
    expect(screen.getByText('pim_common.yes')).toBeInTheDocument();
    expect(screen.getAllByText('pim_common.no')).toHaveLength(2);
    fireEvent.click(screen.getByTitle('pim_common.yes'));
    expect(handleChange).toBeCalledWith(true);
  });

  it('should remove value', () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <BooleanFilterValue
        value={false}
        onChange={handleChange}
        columnCode={'is_allergenic'}
        attribute={getComplexTableAttribute()}
      />
    );

    fireEvent.click(screen.getByTitle('pim_common.clear_value'));
    expect(handleChange).toBeCalledWith(undefined);
  });
});
