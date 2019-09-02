import * as React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneopimenrichmentassetmanager/platform/component/theme';
import {ChannelLabel} from 'akeneopimenrichmentassetmanager/platform/component/channel/channel';

test('It should render the channel label', () => {
  const channelCode = 'ecommerce';
  const locale = 'en_US';
  const channels = [
    {
      code: 'ecommerce',
      labels: {
        en_US: 'E-commerce',
      },
      locales: [
        {
          code: 'en_US',
          label: 'English (United States)',
          language: 'english',
          region: 'United States',
        },
      ],
    },
  ];

  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <ChannelLabel channelCode={channelCode} locale={locale} channels={channels} />
    </ThemeProvider>
  );

  expect(getByText('E-commerce')).toBeInTheDocument();
});

test('It should render the channel code when it has no channels defined', () => {
  const channelCode = 'ecommerce';
  const locale = 'en_US';
  const channels = [];

  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <ChannelLabel channelCode={channelCode} locale={locale} channels={channels} />
    </ThemeProvider>
  );

  expect(getByText('[ecommerce]')).toBeInTheDocument();
});

test('It should render the channel code when it has no channels defined for the current locale', () => {
  const channelCode = 'ecommerce';
  const locale = 'fr_FR';
  const channels = [
    {
      code: 'ecommerce',
      labels: {
        en_US: 'E-commerce',
      },
      locales: [
        {
          code: 'en_US',
          label: 'English (United States)',
          language: 'english',
          region: 'United States',
        },
      ],
    },
  ];

  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <ChannelLabel channelCode={channelCode} locale={locale} channels={channels} />
    </ThemeProvider>
  );

  expect(getByText('[ecommerce]')).toBeInTheDocument();
});
