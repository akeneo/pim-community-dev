import React from 'react';
import {IdentifierAttributeSelector} from '../';
import {render, screen} from '../../tests/test-utils';
import {waitFor} from '@testing-library/react';
import {server} from '../../mocks/server';
import {rest} from 'msw';

describe('IdentifierAttributeSelector', () => {
  it('should render the identifier selector according to the code', async () => {
    render(<IdentifierAttributeSelector code="sku" />);

    await waitFor(() => screen.findByText('Sku'));
    const container = screen.getByTestId('identifierAttribute');
    expect(container).toBeVisible();
    expect(container).toHaveAttribute('readonly');
  });

  it('should show error message when endpoint is forbidden', async () => {
    server.use(
      rest.get('/akeneo_identifier_generator_get_identifier_attributes', (req, res, ctx) => {
        return res(
          ctx.status(403), ctx.json({statusText: 'Forbidden'})
        );
      }),
    );

    render(<IdentifierAttributeSelector code="sku" />);

    await waitFor(() => screen.findByText('pim_error.unauthorized_list_attributes'));
    expect(screen.getByText('pim_error.unauthorized_list_attributes')).toBeVisible();
  });

  it('should show error message when endpoint returns an error', async () => {
    server.use(
      rest.get('/akeneo_identifier_generator_get_identifier_attributes', (req, res, ctx) => {
        return res(
          ctx.status(500), ctx.json({statusText: 'Forbidden'})
        );
      }),
    );

    render(<IdentifierAttributeSelector code="sku" />);

    await waitFor(() => screen.findByText('pim_error.general'));
    expect(screen.getByText('pim_error.general')).toBeVisible();
  });
});
