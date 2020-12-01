import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {LocaleLabel} from 'akeneoassetmanager/platform/component/channel/locale';

test('It should render the locale language with the flag', () => {
  const localeCode = 'en_US';
  const locales = [
    {
      code: 'en_US',
      label: 'English (United States)',
      language: 'English',
      region: 'United States',
    },
  ];

  const {container} = renderWithProviders(<LocaleLabel localeCode={localeCode} locales={locales} />);

  expect(screen.getByText('English')).toBeInTheDocument();
  expect(container.querySelector('i')).toMatchInlineSnapshot(`
    <i
      class="flag flag-us"
    />
  `);
});

test('It should render the locale code when it has no locale defined', () => {
  const localeCode = 'en_US';
  const locales = [];

  renderWithProviders(<LocaleLabel localeCode={localeCode} locales={locales} />);

  expect(screen.getByText('en_US')).toBeInTheDocument();
});

test('It should render the locale code when it has no locale defined for the current locale code', () => {
  const localeCode = 'fr_FR';
  const locales = [
    {
      code: 'en_US',
      label: 'English (United States)',
      language: 'English',
      region: 'United States',
    },
  ];

  renderWithProviders(<LocaleLabel localeCode={localeCode} locales={locales} />);

  expect(screen.getByText('fr_FR')).toBeInTheDocument();
});
