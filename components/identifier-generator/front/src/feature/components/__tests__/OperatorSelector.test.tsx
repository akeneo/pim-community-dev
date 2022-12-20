import React from 'react';
import {render, screen, fireEvent} from '../../tests/test-utils';
import {OperatorSelector} from '../OperatorSelector';

describe('OperatorSelector', () => {
  it('should render the selector', () => {
    const onChange = jest.fn();
    render(<OperatorSelector operator={'IN'} operators={['IN', 'NOT IN']} onChange={onChange} />);

    expect(screen.getByText('pim_common.operators.IN')).toBeInTheDocument();
    fireEvent.click(screen.getByRole('button'));
    expect(screen.getByText('pim_common.operators.NOT IN')).toBeInTheDocument();
    fireEvent.click(screen.getByText('pim_common.operators.NOT IN'));
    expect(onChange).toBeCalledWith('NOT IN');
  });
});
