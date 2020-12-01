import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {ChannelLabel} from 'akeneoassetmanager/platform/component/channel/channel';

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

  renderWithProviders(<ChannelLabel channelCode={channelCode} locale={locale} channels={channels} />);

  expect(screen.getByText('E-commerce')).toBeInTheDocument();
});

test('It should render the channel code when it has no channels defined', () => {
  const channelCode = 'ecommerce';
  const locale = 'en_US';
  const channels = [];

  renderWithProviders(<ChannelLabel channelCode={channelCode} locale={locale} channels={channels} />);

  expect(screen.getByText('[ecommerce]')).toBeInTheDocument();
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

  renderWithProviders(<ChannelLabel channelCode={channelCode} locale={locale} channels={channels} />);

  expect(screen.getByText('[ecommerce]')).toBeInTheDocument();
});
