import React, {ReactNode} from 'react';
import {fireEvent, render, screen} from '../../tests/test-utils';
import {CategoriesLine} from '../conditions';
import {CategoriesCondition, CONDITION_NAMES, Operator} from '../../models';

jest.mock('../../components/CategoriesSelector');

const TableMock = ({children}: {children: ReactNode}) => (
  <table>
    <tbody>
      <tr>{children}</tr>
    </tbody>
  </table>
);

describe('CategoriesLine', () => {
  it('should add values when setting operator to IN', () => {
    const onChange = jest.fn();
    const categoriesCondition: CategoriesCondition = {type: CONDITION_NAMES.CATEGORIES, operator: Operator.CLASSIFIED};
    render(
      <TableMock>
        <CategoriesLine condition={categoriesCondition} onChange={onChange} onDelete={jest.fn()} />
      </TableMock>
    );

    expect(screen.getByText('pim_common.categories')).toBeInTheDocument();
    expect(screen.queryByText('CategoriesSelectorMock')).not.toBeInTheDocument();

    fireEvent.click(screen.getAllByRole('button')[0]);
    fireEvent.click(screen.getByText('pim_common.operators.IN'));

    expect(onChange).toBeCalledWith({
      operator: Operator.IN,
      type: CONDITION_NAMES.CATEGORIES,
      value: [],
    });
  });

  it('should remove values when setting operator to CLASSIFIED', () => {
    const onChange = jest.fn();
    const categoriesCondition: CategoriesCondition = {
      type: CONDITION_NAMES.CATEGORIES,
      operator: Operator.IN,
      value: ['categoryA'],
    };
    render(
      <TableMock>
        <CategoriesLine condition={categoriesCondition} onChange={onChange} onDelete={jest.fn()} />
      </TableMock>
    );

    expect(screen.getByText('CategoriesSelectorMock')).toBeInTheDocument();
    expect(screen.getByText('["categoryA"]')).toBeInTheDocument();

    fireEvent.click(screen.getAllByRole('button')[0]);
    fireEvent.click(screen.getByText('pim_identifier_generator.operators.CLASSIFIED'));

    expect(onChange).toBeCalledWith({
      operator: Operator.CLASSIFIED,
      type: CONDITION_NAMES.CATEGORIES,
    });
  });

  it('should update categories', () => {
    const onChange = jest.fn();
    const categoriesCondition: CategoriesCondition = {
      type: CONDITION_NAMES.CATEGORIES,
      operator: Operator.IN,
      value: [],
    };
    render(
      <TableMock>
        <CategoriesLine condition={categoriesCondition} onChange={onChange} onDelete={jest.fn()} />
      </TableMock>
    );

    expect(screen.getByText('CategoriesSelectorMock')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Set categoryB'));

    expect(onChange).toBeCalledWith({
      operator: Operator.IN,
      type: CONDITION_NAMES.CATEGORIES,
      value: ['categoryB'],
    });
  });
});
