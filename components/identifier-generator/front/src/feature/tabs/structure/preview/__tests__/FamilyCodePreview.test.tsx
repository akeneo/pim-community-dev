import React from 'react';
import {mockResponse, render} from '../../../../tests/test-utils';
import {FamilyCodePreview} from '../FamilyCodePreview';
import {AbbreviationType, FamilyCodeProperty, Operator, PROPERTY_NAMES} from '../../../../models';
import {waitFor} from '@testing-library/react';

describe('FamilyCodePreview', () => {
  beforeEach(() => {
    const page1 = [...Array(20)].map((_, i) => ({code: `Family${i}`, labels: {}}));
    mockResponse('akeneo_identifier_generator_get_families', 'GET', {ok: true, json: page1});
  });

  it('should display the first retrieved family code when process is code', async () => {
    const familyProperty: FamilyCodeProperty = {
      type: PROPERTY_NAMES.FAMILY,
      process: {
        type: AbbreviationType.NO,
      },
    };
    const screen = render(<FamilyCodePreview property={familyProperty} />);

    await waitFor(() => {
      expect(screen.getByText('Family0')).toBeInTheDocument();
    });
  });

  it('should display the first retrieved family code truncated when process is truncate', async () => {
    const familyProperty: FamilyCodeProperty = {
      type: PROPERTY_NAMES.FAMILY,
      process: {
        type: AbbreviationType.TRUNCATE,
        operator: Operator.EQUAL,
        value: 3,
      },
    };
    const screen = render(<FamilyCodePreview property={familyProperty} />);

    await waitFor(() => {
      expect(screen.getByText('Fam')).toBeInTheDocument();
    });
  });
});
