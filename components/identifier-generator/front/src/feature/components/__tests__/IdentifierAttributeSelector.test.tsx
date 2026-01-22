import React from 'react';
import {IdentifierAttributeSelector} from '../';
import {mockResponse, render, screen} from '../../tests/test-utils';
import {waitFor, fireEvent} from '@testing-library/react';

describe('IdentifierAttributeSelector', () => {
  it('should render the identifier selector according to the code', async () => {
    mockResponse('akeneo_identifier_generator_get_identifier_attributes', 'GET', {
      ok: true,
      json: [{code: 'sku', label: 'Sku'}],
    });

    render(<IdentifierAttributeSelector code="sku" onChange={jest.fn()} />);

    await waitFor(() => screen.findByText('Sku'));
    const container = screen.getByTestId('identifierAttribute');
    expect(container).toBeVisible();
  });

  it('should show error message when endpoint is forbidden', async () => {
    mockResponse('akeneo_identifier_generator_get_identifier_attributes', 'GET', {
      ok: false,
      statusText: 'Forbidden',
      json: [],
    });

    render(<IdentifierAttributeSelector code="sku" onChange={jest.fn()} />);

    await waitFor(() => screen.findByText('pim_error.unauthorized_list_attributes'));
    expect(screen.getByText('pim_error.unauthorized_list_attributes')).toBeVisible();
  });

  it('should show error message when endpoint returns an error', async () => {
    mockResponse('akeneo_identifier_generator_get_identifier_attributes', 'GET', {
      ok: false,
      json: [],
      statusText: 'unexpected error',
    });

    render(<IdentifierAttributeSelector code="sku" onChange={jest.fn()} />);

    await waitFor(() => screen.findByText('pim_error.general'));
    expect(screen.getByText('pim_error.general')).toBeVisible();
  });

  it('should update identifier code', async () => {
    const onChange = jest.fn();
    mockResponse('akeneo_identifier_generator_get_identifier_attributes', 'GET', {
      ok: true,
      json: [
        {code: 'sku', label: 'Sku'},
        {code: 'ean', label: 'EAN'},
      ],
    });

    render(<IdentifierAttributeSelector code="sku" onChange={onChange} />);

    await waitFor(() => screen.findByText('Sku'));
    fireEvent.click(screen.getByTitle('pim_common.open'));
    expect(await screen.findByText('EAN')).toBeInTheDocument();
    fireEvent.click(screen.getByText('EAN'));

    expect(onChange).toBeCalledWith('ean');
  });
});
