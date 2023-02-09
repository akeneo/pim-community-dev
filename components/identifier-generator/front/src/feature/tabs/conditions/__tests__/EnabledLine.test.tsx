import React, {ReactNode} from 'react';
import {fireEvent, render, screen} from '../../../tests/test-utils';
import {EnabledLine} from '../EnabledLine';
import {CONDITION_NAMES} from '../../../models';

const TableMock = ({children}: {children: ReactNode}) => (
  <table>
    <tbody>
      <tr>{children}</tr>
    </tbody>
  </table>
);

describe('EnabledLine', () => {
  it('should render the name, operator and value', () => {
    render(
      <TableMock>
        <EnabledLine
          condition={{type: CONDITION_NAMES.ENABLED, value: true}}
          onChange={jest.fn()}
          onDelete={jest.fn()}
        />
      </TableMock>
    );

    expect(screen.getByText('pim_common.status')).toBeInTheDocument();
    expect(screen.getByText('pim_common.operators.=')).toBeInTheDocument();
    expect(screen.getByText('pim_common.enabled')).toBeInTheDocument();
  });

  it('should render with empty condition value', () => {
    render(
      <TableMock>
        <EnabledLine
          condition={{type: CONDITION_NAMES.ENABLED, value: undefined}}
          onChange={jest.fn()}
          onDelete={jest.fn()}
        />
      </TableMock>
    );

    expect(screen.getByText('pim_common.status')).toBeInTheDocument();
    expect(screen.getByText('pim_common.operators.=')).toBeInTheDocument();
    expect(screen.queryByText('pim_common.enabled')).not.toBeInTheDocument;
  });

  it('should callback on change', () => {
    const onChange = jest.fn();
    render(
      <TableMock>
        <EnabledLine
          condition={{type: CONDITION_NAMES.ENABLED, value: false}}
          onChange={onChange}
          onDelete={jest.fn()}
        />
      </TableMock>
    );

    const buttons = screen.getAllByRole('button');
    const openButton = buttons.find(button => button.title === 'pim_common.open') as HTMLElement;
    fireEvent.click(openButton);
    fireEvent.click(screen.getByTitle('pim_common.enabled'));
    expect(onChange).toBeCalledWith({type: CONDITION_NAMES.ENABLED, value: true});
  });

  it('should callback on delete', () => {
    const onDelete = jest.fn();
    render(
      <TableMock>
        <EnabledLine
          condition={{type: CONDITION_NAMES.ENABLED, value: false}}
          onChange={jest.fn()}
          onDelete={onDelete}
        />
      </TableMock>
    );

    const deleteButton = screen.getByText('pim_common.delete');
    expect(deleteButton).toBeInTheDocument();
    fireEvent.click(deleteButton);
    expect(onDelete).toBeCalled();
  });
});
