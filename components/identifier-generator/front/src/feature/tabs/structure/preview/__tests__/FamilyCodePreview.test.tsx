import React from 'react';
import {render} from '../../../../tests/test-utils';
import {FamilyCodePreview} from '../FamilyCodePreview';
import {AbbreviationType, FamilyProperty, Operator, PROPERTY_NAMES} from '../../../../models';
import {waitFor} from '@testing-library/react';

describe('FamilyCodePreview', () => {
  it('should display the first retrieved family code when process is code', async () => {
    const familyProperty: FamilyProperty = {
      type: PROPERTY_NAMES.FAMILY,
      process: {
        type: AbbreviationType.NO,
      },
    };
    const screen = render(<FamilyCodePreview property={familyProperty} />);

    await waitFor(() => {
      expect(screen.getByText('Family')).toBeInTheDocument();
    });
  });

  it('should display the first retrieved family code truncated when process is truncate', async () => {
    const familyProperty: FamilyProperty = {
      type: PROPERTY_NAMES.FAMILY,
      process: {
        type: AbbreviationType.TRUNCATE,
        operator: Operator.EQUALS,
        value: 3,
      },
    };
    const screen = render(<FamilyCodePreview property={familyProperty} />);

    await waitFor(() => {
      expect(screen.getByText('Fam')).toBeInTheDocument();
    });
  });
});
