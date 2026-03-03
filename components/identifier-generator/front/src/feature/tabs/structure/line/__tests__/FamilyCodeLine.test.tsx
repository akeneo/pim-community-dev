import React from 'react';
import {render} from '../../../../tests/test-utils';
import {FamilyCodeLine} from '../FamilyCodeLine';

describe('FamilyCodeLine', () => {
  it('should display family line', () => {
    const screen = render(<FamilyCodeLine />);

    expect(screen.getByText('pim_identifier_generator.structure.settings.family.title')).toBeInTheDocument();
  });
});
