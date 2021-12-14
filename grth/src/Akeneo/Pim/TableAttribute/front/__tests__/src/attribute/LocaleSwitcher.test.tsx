import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {LocaleSwitcher} from '../../../src';
import {act, fireEvent, screen} from '@testing-library/react';
import {getLocales} from '../../factories';

jest.mock('../../../src/fetchers/LocaleFetcher');

describe('LocaleSwitcher', () => {
  it('should render a locale', () => {
    act(() => {
      renderWithProviders(<LocaleSwitcher localeCode={'en_US'} onChange={jest.fn()} locales={getLocales()} />);
    });

    expect(screen.getByText('English')).toBeInTheDocument();
  });

  it('should display nothing if locale does not exists', async () => {
    act(() => {
      renderWithProviders(<LocaleSwitcher localeCode={'en_US'} onChange={jest.fn()} locales={[]} />);
    });

    expect(screen.queryByText('English')).not.toBeInTheDocument();
  });

  it('should change a locale', async () => {
    const handleChange = jest.fn();
    act(() => {
      renderWithProviders(<LocaleSwitcher localeCode={'en_US'} onChange={handleChange} locales={getLocales()} />);
    });

    act(() => {
      fireEvent.click(screen.getByText('English'));
    });
    expect(await screen.findByText('French')).toBeInTheDocument();
    fireEvent.click(screen.getByText('French'));
    expect(handleChange).toBeCalledWith('fr_FR');
  });
});
