import React from 'react';
import {mockResponse, render} from '../../../tests/test-utils';
import {ImplicitConditionsList} from '../ImplicitConditionsList';
import {AbbreviationType, IdentifierGenerator, PROPERTY_NAMES} from '../../../models';
import initialGenerator from '../../../tests/fixtures/initialGenerator';
import {waitFor} from '@testing-library/react';

jest.mock('../ImplicitSimpleSelectCondition');

const mockedGenerator: IdentifierGenerator = {
  ...initialGenerator,
};

describe('ImplicitConditionsList', () => {
  beforeEach(() => {
    mockResponse('akeneo_identifier_generator_get_identifier_attributes', 'GET', {
      json: [{code: 'sku', label: 'Sku'}],
    });
  });
  it('should display sku as default user added condition', async () => {
    const screen = render(<ImplicitConditionsList generator={mockedGenerator} />);

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
    const screen = render(<ImplicitConditionsList generator={generator} />);

    await waitFor(() => {
      expect(screen.getByText('pim_identifier_generator.structure.property_type.family')).toBeInTheDocument();
    });
  });

  it('should add simple selects not empty when there are simple select structure properties', async () => {
    const generator: IdentifierGenerator = {
      ...initialGenerator,
      structure: [
        {
          type: PROPERTY_NAMES.SIMPLE_SELECT,
          attributeCode: 'color',
          process: {type: AbbreviationType.NO},
        },
        {
          type: PROPERTY_NAMES.SIMPLE_SELECT,
          attributeCode: 'brand',
          scope: 'ecommerce',
          locale: 'en_US',
          process: {type: AbbreviationType.NO},
        },
      ],
    };
    const screen = render(<ImplicitConditionsList generator={generator} />);

    await waitFor(() => {
      expect(screen.getAllByText('ImplicitSimpleSelectConditionMocked').length).toEqual(2);
    });

    expect(screen.getAllByText('Implicit attribute scope:').length).toEqual(1);
    expect(screen.getAllByText('Implicit attribute locale:').length).toEqual(1);

    expect(screen.getByText('Implicit attribute code: color')).toBeInTheDocument();
    expect(screen.getByText('Implicit attribute code: brand')).toBeInTheDocument();
    expect(screen.getByText('ecommerce')).toBeInTheDocument();
    expect(screen.getByText('en_US')).toBeInTheDocument();
  });
});
