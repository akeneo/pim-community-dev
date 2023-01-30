import React from 'react';
import {mockResponse, render} from '../../../tests/test-utils';
import {UserAddedConditionsList} from '../UserAddedConditionsList';
import {AbbreviationType, IdentifierGenerator, PROPERTY_NAMES} from '../../../models';
import initialGenerator from '../../../tests/fixtures/initialGenerator';
import {waitFor} from '@testing-library/react';

const mockedGenerator: IdentifierGenerator = {
  ...initialGenerator,
};

describe('UserAddedConditionsList', () => {
  beforeEach(() => {
    mockResponse('akeneo_identifier_generator_get_identifier_attributes', 'GET', {
      json: [{code: 'sku', label: 'Sku'}],
    });
  });
  it('should display sku as default user added condition', async () => {
    const screen = render(<UserAddedConditionsList generator={mockedGenerator} />);

    expect(screen.getAllByText('This is a loading label')).toHaveLength(3);
    await waitFor(() => {
      expect(screen.getByText('Sku')).toBeInTheDocument();
    });
  });

  it('should add family not empty when there is a family structure property', async () => {
    const generator: IdentifierGenerator = {
      ...initialGenerator,
      structure: [{type: PROPERTY_NAMES.FAMILY, process: {type: AbbreviationType.NO}}],
    };
    const screen = render(<UserAddedConditionsList generator={generator} />);

    await waitFor(() => {
      expect(screen.getByText('Family')).toBeInTheDocument();
    });
  });
});
