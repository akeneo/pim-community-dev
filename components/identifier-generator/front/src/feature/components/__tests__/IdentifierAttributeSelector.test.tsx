import React from 'react';
import {IdentifierAttributeSelector} from '../';
import {render, screen} from '../../tests/test-utils';
import {waitFor} from '@testing-library/react';

describe('IdentifierAttributeSelector', () => {
  it('should render the identifier selector according to the code', async () => {
    // @ts-ignore
    jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: true,
      json: () => Promise.resolve([{code: 'sku', label: 'Sku'}]),
    });

    render(<IdentifierAttributeSelector code="sku" />);

    await waitFor(() => screen.findByText('Sku'));
    const container = screen.getByTestId('identifierAttribute');
    expect(container).toBeVisible();
    expect(container).toHaveAttribute('readonly');
  });

  it('should show error message when endpoint is forbidden', async () => {
    const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
    // @ts-ignore;
    jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: false,
      statusText: 'Forbidden',
      json: () => Promise.resolve([]),
    });

    render(<IdentifierAttributeSelector code="sku" />);

    await waitFor(() => screen.findByText('pim_error.unauthorized'));
    expect(screen.getByText('pim_error.unauthorized')).toBeVisible();
    mockedConsole.mockRestore();
  });

  it('should show error message when endpoint returns an error', async () => {
    const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
    // @ts-ignore
    jest.spyOn(global, 'fetch').mockImplementation(input => {
      if (input === 'akeneo_identifier_generator_get_identifier_attributes') {
        return Promise.reject({message: 'unexpected error'});
      }
      return Promise.resolve({
        json: () => Promise.resolve([]),
      });
    });

    render(<IdentifierAttributeSelector code="sku" />);

    await waitFor(() => screen.findByText('pim_error.general'));
    expect(screen.getByText('pim_error.general')).toBeVisible();
    mockedConsole.mockRestore();
  });
});
