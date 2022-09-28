import React from 'react';
import {IdentifierGeneratorApp} from '../IdentifierGeneratorApp';
import {render, screen, act, fireEvent} from '../tests/test-utils';

describe('IdentifierGeneratorApp', () => {
  it('is just an example of unit test', () => {
    render(<IdentifierGeneratorApp />);

    expect(screen.getAllByText('Identifier generators')).toHaveLength(2);
    act(() => {
      fireEvent.click(screen.getByText('Create'));
    });
  });
});
