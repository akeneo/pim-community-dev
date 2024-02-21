import React from 'react';
import {mockResponse, render, waitFor} from '../../../../tests/test-utils';
import {AttributePropertyLine} from '../AttributePropertyLine';
import {PROPERTY_NAMES, SimpleSelectProperty} from '../../../../models';

describe('AttributePropertyLine', () => {
  it('should display simple select line', async () => {
    mockResponse('pim_enrich_attribute_rest_get', 'GET', {
      ok: true,
      json: {
        code: 'brand',
        labels: {en_US: 'Brand'},
        localizable: false,
        scopable: false,
        type: 'pim_catalog_simpleselect',
      },
    });

    const simpleSelectProperty: SimpleSelectProperty = {
      type: PROPERTY_NAMES.SIMPLE_SELECT,
      attributeCode: 'brand',
      process: {type: null},
      scope: null,
      locale: null,
    };
    const screen = render(<AttributePropertyLine attributeCode={simpleSelectProperty.attributeCode} />);

    await waitFor(() => {
      expect(screen.getByText('Brand')).toBeInTheDocument();
    });
  });
});
