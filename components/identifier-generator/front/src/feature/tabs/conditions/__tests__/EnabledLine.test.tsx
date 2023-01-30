import React from 'react';
import {fireEvent, render, screen} from '../../../tests/test-utils';
import {EnabledLine} from '../EnabledLine';
import {CONDITION_NAMES} from '../../../models';

describe('EnabledLine', () => {
  it('should render the name, operator and value', () => {
    render(
      <table>
        <tbody>
          <tr>
            <EnabledLine
              condition={{type: CONDITION_NAMES.ENABLED, value: true, id: 'enabledId'}}
              onChange={jest.fn()}
              onDelete={jest.fn()}
            />
          </tr>
        </tbody>
      </table>
    );

    expect(screen.getByText('pim_common.status')).toBeInTheDocument();
    expect(screen.getByTitle('pim_common.operators.=')).toBeInTheDocument();
    expect(screen.getByText('pim_common.enabled')).toBeInTheDocument();
  });

  it('should render with empty condition value', () => {
    render(
      <table>
        <tbody>
          <tr>
            <EnabledLine
              condition={{type: CONDITION_NAMES.ENABLED, value: undefined, id: 'enabledId'}}
              onChange={jest.fn()}
            />
          </tr>
        </tbody>
      </table>
    );

    expect(screen.getByText('pim_common.status')).toBeInTheDocument();
    expect(screen.getByTitle('pim_common.operators.=')).toBeInTheDocument();
    expect(screen.queryByText('pim_common.enabled')).not.toBeInTheDocument;
  });

  it('should callback on change', () => {
    const onChange = jest.fn();
    render(
      <table>
        <tbody>
          <tr>
            <EnabledLine
              condition={{type: CONDITION_NAMES.ENABLED, value: false, id: 'enabledId'}}
              onChange={onChange}
              onDelete={jest.fn()}
            />
          </tr>
        </tbody>
      </table>
    );

    const buttons = screen.getAllByRole('button');
    const openButton = buttons.find(button => button.title === 'pim_common.open') as HTMLElement;
    fireEvent.click(openButton);
    fireEvent.click(screen.getByTitle('pim_common.enabled'));
    expect(onChange).toBeCalledWith({type: CONDITION_NAMES.ENABLED, value: true, id: 'enabledId'});
  });

  it('should callback on delete', () => {
    const onDelete = jest.fn();
    render(
      <table>
        <tbody>
          <tr>
            <EnabledLine
              condition={{type: CONDITION_NAMES.ENABLED, value: false, id: 'enabledId'}}
              onChange={jest.fn()}
              onDelete={onDelete}
            />
          </tr>
        </tbody>
      </table>
    );

    const deleteButton = screen.getByText('pim_common.delete');
    expect(deleteButton).toBeInTheDocument();
    fireEvent.click(deleteButton);
    expect(onDelete).toBeCalled();
  });
});
