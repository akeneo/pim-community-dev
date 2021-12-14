import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {act, screen, fireEvent} from '@testing-library/react';
import {OperatorSelector} from '../../../src/datagrid';

describe('OperatorSelector', () => {
  it('should display current operator', () => {
    renderWithProviders(<OperatorSelector dataType={'number'} value={'>='} onChange={jest.fn()} />);

    expect(screen.getByText('pim_common.operators.>=')).toBeInTheDocument();
  });

  it('should display all operators, then update it', () => {
    const handleChange = jest.fn();
    renderWithProviders(<OperatorSelector dataType={'number'} value={'>='} onChange={handleChange} />);

    act(() => {
      fireEvent.click(screen.getByTitle('pim_common.open'));
    });
    expect(screen.getByText('pim_common.operators.>')).toBeInTheDocument();
    expect(screen.getAllByText('pim_common.operators.>=')).toHaveLength(2);
    expect(screen.getByText('pim_common.operators.<')).toBeInTheDocument();
    expect(screen.getByText('pim_common.operators.<=')).toBeInTheDocument();
    expect(screen.getByText('pim_common.operators.=')).toBeInTheDocument();
    expect(screen.getByText('pim_common.operators.!=')).toBeInTheDocument();
    expect(screen.getByText('pim_common.operators.EMPTY')).toBeInTheDocument();
    expect(screen.getByText('pim_common.operators.NOT EMPTY')).toBeInTheDocument();
    fireEvent.click(screen.getByText('pim_common.operators.NOT EMPTY'));
    expect(handleChange).toBeCalledWith('NOT EMPTY');
  });
});
