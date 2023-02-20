import React, {ReactNode} from 'react';
import {fireEvent, render, screen} from '../../tests/test-utils';
import {FamilyLine} from '../conditions';
import {CONDITION_NAMES, FamilyCondition, Operator} from '../../models';

jest.mock('../../components/FamiliesSelector');

const TableMock = ({children}: {children: ReactNode}) => (
  <table>
    <tbody>
      <tr>{children}</tr>
    </tbody>
  </table>
);

describe('FamilyLine', () => {
  it('should add values when setting operator to IN', () => {
    const onChange = jest.fn();
    const familyCondition: FamilyCondition = {type: CONDITION_NAMES.FAMILY, operator: Operator.EMPTY};
    render(
      <TableMock>
        <FamilyLine condition={familyCondition} onChange={onChange} onDelete={jest.fn()} />
      </TableMock>
    );

    expect(screen.getByText('pim_common.family')).toBeInTheDocument();
    expect(screen.queryByText('FamiliesSelectorMock')).not.toBeInTheDocument();

    fireEvent.click(screen.getAllByRole('button')[0]);
    fireEvent.click(screen.getByText('pim_common.operators.IN'));

    expect(onChange).toBeCalledWith({
      operator: Operator.IN,
      type: CONDITION_NAMES.FAMILY,
      value: [],
    });
  });

  it('should remove values when setting operator to EMPTY', () => {
    const onChange = jest.fn();
    const familyCondition: FamilyCondition = {type: CONDITION_NAMES.FAMILY, operator: Operator.IN, value: ['shirts']};
    render(
      <TableMock>
        <FamilyLine condition={familyCondition} onChange={onChange} onDelete={jest.fn()} />
      </TableMock>
    );

    expect(screen.getByText('FamiliesSelectorMock')).toBeInTheDocument();
    expect(screen.getByText('["shirts"]')).toBeInTheDocument();

    fireEvent.click(screen.getAllByRole('button')[0]);
    fireEvent.click(screen.getByText('pim_common.operators.EMPTY'));

    expect(onChange).toBeCalledWith({
      operator: Operator.EMPTY,
      type: CONDITION_NAMES.FAMILY,
    });
  });

  it('should update families', () => {
    const onChange = jest.fn();
    const familyCondition: FamilyCondition = {type: CONDITION_NAMES.FAMILY, operator: Operator.IN, value: []};
    render(
      <TableMock>
        <FamilyLine condition={familyCondition} onChange={onChange} onDelete={jest.fn()} />
      </TableMock>
    );

    expect(screen.getByText('FamiliesSelectorMock')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Set shirts'));

    expect(onChange).toBeCalledWith({
      operator: Operator.IN,
      type: CONDITION_NAMES.FAMILY,
      value: ['shirts'],
    });
  });
});
