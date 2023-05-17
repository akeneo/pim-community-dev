import React from 'react';
import {render} from '../../tests/test-utils';
import {ScopeSelector} from '../ScopeSelector';
import {fireEvent, waitFor} from '@testing-library/react';
import {server} from '../../mocks/server';
import {rest} from 'msw';

describe('ScopeSelector', () => {
  it('should render select with channels', async () => {
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
    const screen = render(<ScopeSelector value={'ecommerce'} onChange={jest.fn()} />);

    await waitFor(() => expect(screen.getByText('Ecommerce')).toBeInTheDocument());
  });

  it('should render default error', async () => {
    server.use(
      rest.get('/pim_enrich_channel_rest_index', (req, res, ctx) => {
        return res(ctx.status(500));
      })
    );

    const screen = render(<ScopeSelector value={null} onChange={jest.fn()} />);

    expect(await screen.findByText('pim_error.general')).toBeInTheDocument();
  });
});
