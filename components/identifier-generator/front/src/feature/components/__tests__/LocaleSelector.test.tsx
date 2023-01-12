import React from 'react';
import {mockResponse, render} from '../../tests/test-utils';
import {LocaleSelector} from '../LocaleSelector';
import {fireEvent, waitFor} from '@testing-library/react';
import mockedScopes from '../../tests/fixtures/scopes';

describe('LocaleSelector', () => {
  beforeEach(() => {
    mockResponse('pim_enrich_channel_rest_index', 'GET', {
      ok: true,
      json: mockedScopes,
    });
  });

  it('should display ui locales if not scopable', async () => {
    const mockedOnChange = jest.fn();
    const screen = render(<LocaleSelector value={null} onChange={mockedOnChange} scopable={false} />);

    await waitFor(() => {
      expect(screen.getByTitle('pim_common.locale')).toBeInTheDocument();
    });
    fireEvent.click(screen.getByTitle('pim_common.locale'));
    expect(screen.getByText('French (France)')).toBeInTheDocument();
    fireEvent.click(screen.getByText('French (France)'));
    expect(mockedOnChange).toHaveBeenCalledWith('fr_FR');
  });

  it('should display scope locales if scopable', async () => {
    const mockedOnChange = jest.fn();
    const screen = render(
      <LocaleSelector value={null} onChange={mockedOnChange} scopable={true} scope={mockedScopes[0]} />
    );

    await waitFor(() => {
      expect(screen.getByTitle('pim_common.locale')).toBeInTheDocument();
    });
    fireEvent.click(screen.getByTitle('pim_common.locale'));
    expect(screen.getByText('French (France)')).toBeInTheDocument();
    fireEvent.click(screen.getByText('French (France)'));
    expect(mockedOnChange).toHaveBeenCalledWith('fr_FR');
  });

  it('should display empty locales if localizable but no scope is provided', async () => {
    const mockedOnChange = jest.fn();
    const screen = render(<LocaleSelector value={null} onChange={mockedOnChange} scopable={true} />);

    await waitFor(() => {
      expect(screen.getByTitle('pim_common.locale')).toBeInTheDocument();
    });
    fireEvent.click(screen.getByTitle('pim_common.locale'));
    expect(screen.getByText('pim_common.no_result')).toBeInTheDocument();
    expect(screen.queryByText('French (France)')).not.toBeInTheDocument();
  });

  it('should display an error when getUiLocales fails', async () => {
    mockResponse('pim_localization_locale_index', 'GET', {
      ok: false,
      json: [],
      status: 500,
    });
    const screen = render(<LocaleSelector value={null} onChange={jest.fn()} scopable={true} />);
    await waitFor(() => {
      expect(screen.getByText('pim_error.general')).toBeInTheDocument();
    });
  });
});
