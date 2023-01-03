import React from 'react';
import {fireEvent, render, screen} from '../../../tests/test-utils';
import {EnabledLine} from '../EnabledLine';
import {CONDITION_NAMES} from '../../../models';

describe('EnabledLine', () => {
  it('should render the name, operator and value', () => {
    render(
      <table>
        <tbody>
          <EnabledLine condition={{type: CONDITION_NAMES.ENABLED, value: true, id: 'enabledId'}} onChange={jest.fn()} />
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
          <EnabledLine
            condition={{type: CONDITION_NAMES.ENABLED, value: undefined, id: 'enabledId'}}
            onChange={jest.fn()}
          />
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
          <EnabledLine condition={{type: CONDITION_NAMES.ENABLED, value: false, id: 'enabledId'}} onChange={onChange} />
        </tbody>
      </table>
    );

    fireEvent.click(screen.getByRole('button'));
    fireEvent.click(screen.getByTitle('pim_common.enabled'));
    expect(onChange).toBeCalledWith({type: CONDITION_NAMES.ENABLED, value: true, id: 'enabledId'});
  });
});
