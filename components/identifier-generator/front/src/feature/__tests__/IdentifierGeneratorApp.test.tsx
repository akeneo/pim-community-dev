import React from 'react';
import {IdentifierGeneratorApp} from '../';
import {render, screen, act, fireEvent} from '../tests/test-utils';

describe('IdentifierGeneratorApp', () => {
  it('is just an example of unit test', () => {
    render(<IdentifierGeneratorApp />);

    expect(screen.getAllByText('pim_title.akeneo_identifier_generator_index')).toHaveLength(2);
    act(() => {
      fireEvent.click(screen.getByText('pim_common.create'));
    });
  });
});
