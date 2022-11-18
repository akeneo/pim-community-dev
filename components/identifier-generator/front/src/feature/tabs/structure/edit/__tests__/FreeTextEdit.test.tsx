import React from 'react';
import {fireEvent, render, screen} from '../../../../tests/test-utils';
import {FreeText, PROPERTY_NAMES} from '../../../../models';
import {FreeTextEdit} from '../FreeTextEdit';

describe('FreeTextEdit', () => {
  it('calls the callback on change', () => {
    const selectedProperty: FreeText = {
      type: PROPERTY_NAMES.FREE_TEXT,
      string: 'initial string',
    };
    const onChange = jest.fn();
    render(<FreeTextEdit selectedProperty={selectedProperty} onChange={onChange} />);

    fireEvent.change(screen.getByTitle('initial string'), {target: {value: 'updated string'}});
    expect(onChange).toBeCalledWith({
      type: PROPERTY_NAMES.FREE_TEXT,
      string: 'updated string',
    });
  });
});
