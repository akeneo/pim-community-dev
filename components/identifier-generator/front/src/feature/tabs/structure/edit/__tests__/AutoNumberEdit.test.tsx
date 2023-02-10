import React from 'react';
import {fireEvent, render, screen} from '../../../../tests/test-utils';
import {AutoNumber, PROPERTY_NAMES} from '../../../../models';
import {AutoNumberEdit} from '../AutoNumberEdit';

describe('AutoNumberEdit', () => {
  it('calls the callback on change', () => {
    const selectedProperty: AutoNumber = {
      type: PROPERTY_NAMES.AUTO_NUMBER,
      numberMin: 42,
      digitsMin: 10,
    };
    const onChange = jest.fn();
    render(<AutoNumberEdit selectedProperty={selectedProperty} onChange={onChange} />);

    fireEvent.change(screen.getByTitle('42'), {target: {value: '69'}});
    expect(onChange).toBeCalledWith({
      type: PROPERTY_NAMES.AUTO_NUMBER,
      numberMin: 69,
      digitsMin: 10,
    });

    fireEvent.change(screen.getByTitle('10'), {target: {value: '5'}});
    expect(onChange).toBeCalledWith({
      type: PROPERTY_NAMES.AUTO_NUMBER,
      numberMin: 42,
      digitsMin: 5,
    });

    fireEvent.change(screen.getByTitle('42'), {target: {value: ''}});
    expect(onChange).toBeCalledWith({
      type: PROPERTY_NAMES.AUTO_NUMBER,
      numberMin: null,
      digitsMin: 10,
    });

    fireEvent.change(screen.getByTitle('10'), {target: {value: ''}});
    expect(onChange).toBeCalledWith({
      type: PROPERTY_NAMES.AUTO_NUMBER,
      numberMin: 42,
      digitsMin: null,
    });
  });
});
