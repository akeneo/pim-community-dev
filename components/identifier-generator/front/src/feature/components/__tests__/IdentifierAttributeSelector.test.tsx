import React from 'react';
import {IdentifierAttributeSelector} from '../IdentifierAttributeSelector';
import {render, screen} from '../../tests/test-utils';
import {waitFor} from '@testing-library/react';
import {setLogger} from 'react-query';

jest.mock('@akeneo-pim-community/shared', () => ({
  ...jest.requireActual('@akeneo-pim-community/shared'),
  useTranslate: () => ((key: string) =>  key)
}));

setLogger({
  log: console.log,
  warn: console.warn,
  // no more errors on the console
  error: () => {},
});

describe('IdentifierAttributeSelector', () => {
  it('should render the identifier selector according to the code', async () => {
    // @ts-ignore;
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
    // @ts-ignore;
    jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: false,
      statusText: 'Forbidden',
      json: () => Promise.resolve([]),
    });

    render(<IdentifierAttributeSelector code="sku" />);

    await waitFor(() => screen.findByText('pim_error.unauthorized'));
    expect(screen.getByText('pim_error.unauthorized')).toBeVisible();
  });

  it('should show error message when endpoint returns an error', async () => {
    // @ts-ignore
    jest.spyOn(global, 'fetch').mockImplementation(input => {
      if (input === '/identifier-generator/identifier-attributes') {
        return Promise.reject({message: 'unexpected error'});
      }
      return Promise.resolve({
        json: () => Promise.resolve([]),
      });
    });

    render(<IdentifierAttributeSelector code="sku" />);

    await waitFor(() => screen.findByText('pim_error.general'));
    expect(screen.getByText('pim_error.general')).toBeVisible();
  });
});
