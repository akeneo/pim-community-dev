import React from 'react';
import {fireEvent, render, screen} from '../../tests/test-utils';
import {OperatorSelector} from '../OperatorSelector';
import {Operator} from '../../models';

describe('OperatorSelector', () => {
  it('should render the selector', () => {
    const onChange = jest.fn();
    render(
      <OperatorSelector operator={Operator.IN} operators={[Operator.IN, Operator.CLASSIFIED]} onChange={onChange} />
    );

    expect(screen.getByText('pim_common.operators.IN')).toBeInTheDocument();
    fireEvent.click(screen.getByRole('button'));
    expect(screen.getByText('pim_identifier_generator.operators.CLASSIFIED')).toBeInTheDocument();
    fireEvent.click(screen.getByText('pim_identifier_generator.operators.CLASSIFIED'));
    expect(onChange).toBeCalledWith(Operator.CLASSIFIED);
  });
});
