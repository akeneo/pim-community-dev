import React from 'react';
import {mockResponse, render} from '../../tests/test-utils';
import {ScopeSelector} from '../ScopeSelector';
import {fireEvent, waitFor} from '@testing-library/react';
import mockedScopes from '../../tests/fixtures/scopes';

describe('ScopeSelector', () => {
  it('should render select with channels', async () => {
    mockResponse('pim_enrich_channel_rest_index', 'GET', {
      ok: true,
      json: () => mockedScopes,
    });
    const onChange = jest.fn();
    const screen = render(<ScopeSelector value={null} onChange={onChange} />);

    expect(screen.getByText('This is a loading channel')).toBeInTheDocument();

    await waitFor(() => expect(screen.getByPlaceholderText('pim_common.channel')).toBeInTheDocument());

    fireEvent.click(screen.getByPlaceholderText('pim_common.channel'));

    expect(screen.getByText('Ecommerce')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Ecommerce'));
    expect(onChange).toBeCalledWith('ecommerce');
  });

  it('should render select with value', async () => {
    mockResponse('pim_enrich_channel_rest_index', 'GET', {
      ok: true,
      json: () => mockedScopes,
    });
    const screen = render(<ScopeSelector value={'ecommerce'} onChange={jest.fn()} />);

    await waitFor(() => expect(screen.getByText('Ecommerce')).toBeInTheDocument());
  });

  it('should render default error', async () => {
    mockResponse('pim_enrich_channel_rest_index', 'GET', {
      ok: false,
      status: 500,
    });

    const screen = render(<ScopeSelector value={null} onChange={jest.fn()} />);

    expect(await screen.findByText('pim_error.general')).toBeInTheDocument();
  });
});
