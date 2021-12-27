import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {LocaleLabel} from '../../../src';
import {act, screen} from '@testing-library/react';

jest.mock('../../../src/fetchers/LocaleFetcher');

describe('LocaleLabel', () => {
  it('should render a locale', async () => {
    act(() => {
      renderWithProviders(<LocaleLabel localeCode={'en_US'} />);
    });
    expect(await screen.findByText('English')).toBeInTheDocument();
  });

  it('should not render a locale', async () => {
    act(() => {
      renderWithProviders(<LocaleLabel localeCode={'pt_DTC'} />);
    });
    expect(screen.queryByText('English')).not.toBeInTheDocument();
    expect(await screen.findByText('pt_DTC')).toBeInTheDocument();
  });
});
