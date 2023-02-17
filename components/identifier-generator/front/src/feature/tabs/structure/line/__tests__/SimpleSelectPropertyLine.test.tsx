import React from 'react';
import {render} from '../../../../tests/test-utils';
import {SimpleSelectPropertyLine} from '../SimpleSelectPropertyLine';

describe('SimpleSelectPropertyLine', () => {
  it('should display simple select line', () => {
    const screen = render(<SimpleSelectPropertyLine />);

    expect(screen.getByText('pim_identifier_generator.structure.settings.simple_select.title')).toBeInTheDocument();
  });
});
