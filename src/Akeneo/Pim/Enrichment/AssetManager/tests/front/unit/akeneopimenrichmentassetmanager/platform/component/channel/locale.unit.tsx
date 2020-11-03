import * as React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import {LocaleLabel} from 'akeneoassetmanager/platform/component/channel/locale';
import console = require('console');
import {debug} from 'util';

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

  const {container, getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <LocaleLabel localeCode={localeCode} locales={locales} />
    </ThemeProvider>
  );

  expect(getByText('English')).toBeInTheDocument();
  expect(container.querySelector('i')).toMatchInlineSnapshot(`
    <i
      class="flag flag-us"
    />
  `);
});

test('It should render the locale code when it has no locale defined', () => {
  const localeCode = 'en_US';
  const locales = [];

  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <LocaleLabel localeCode={localeCode} locales={locales} />
    </ThemeProvider>
  );

  expect(getByText('en_US')).toBeInTheDocument();
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

  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <LocaleLabel localeCode={localeCode} locales={locales} />
    </ThemeProvider>
  );

  expect(getByText('fr_FR')).toBeInTheDocument();
});
