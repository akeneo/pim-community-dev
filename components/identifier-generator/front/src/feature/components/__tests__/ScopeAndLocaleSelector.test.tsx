import React from 'react';
import {ScopeAndLocaleSelector} from '../ScopeAndLocaleSelector';
import {mockResponse, render, waitFor} from '../../tests/test-utils';

describe('ScopeAndLocaleSelector', () => {
  it('should render error', async () => {
    mockResponse('pim_enrich_attribute_rest_get', 'GET', {
      ok: false,
      json: () => null,
      status: 500,
    });
    const screen = render(
      <ScopeAndLocaleSelector attributeCode={'brand'} onChange={jest.fn()} locale={null} scope={null} />
    );

    await waitFor(() => {
      expect(screen.getByText('pim_error.general')).toBeInTheDocument();
    });
  });
});
