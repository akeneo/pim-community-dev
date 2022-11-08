import React from 'react';
import {IdentifierAttributeSelector} from '../';
import {mockResponse, render, screen} from '../../tests/test-utils';
import {waitFor} from '@testing-library/react';

describe('IdentifierAttributeSelector', () => {
  it('should render the identifier selector according to the code', async () => {
    mockResponse('akeneo_identifier_generator_get_identifier_attributes', 'GET', {
      ok: true,
      json: [{code: 'sku', label: 'Sku'}],
    });

    render(<IdentifierAttributeSelector code="sku" />);

    await waitFor(() => screen.findByText('Sku'));
    const container = screen.getByTestId('identifierAttribute');
    expect(container).toBeVisible();
    expect(container).toHaveAttribute('readonly');
  });

  it('should show error message when endpoint is forbidden', async () => {
    mockResponse('akeneo_identifier_generator_get_identifier_attributes', 'GET', {
      ok: false,
      statusText: 'Forbidden',
      json: [],
    });

    render(<IdentifierAttributeSelector code="sku" />);

    await waitFor(() => screen.findByText('pim_error.unauthorized'));
    expect(screen.getByText('pim_error.unauthorized')).toBeVisible();
  });

  it('should show error message when endpoint returns an error', async () => {
    mockResponse('akeneo_identifier_generator_get_identifier_attributes', 'GET', {
      ok: false,
      json: [],
      statusText: 'unexpected error',
    });

    render(<IdentifierAttributeSelector code="sku" />);

    await waitFor(() => screen.findByText('pim_error.general'));
    expect(screen.getByText('pim_error.general')).toBeVisible();
  });
});
