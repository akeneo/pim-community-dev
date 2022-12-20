import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {ChannelSelector} from './ChannelSelector';
import userEvent from '@testing-library/user-event';
import {Channel} from '@akeneo-pim-community/shared';

const locales = [
  {
    code: 'en_US',
    label: 'English (United States)',
    region: 'United States',
    language: 'English',
  },
  {
    code: 'fr_FR',
    label: 'French (France)',
    region: 'France',
    language: 'French',
  },
];

const channels: Channel[] = [
  {
    code: 'ecommerce',
    labels: {en_US: 'Ecommerce'},
    locales: locales,
    category_tree: '1',
    conversion_units: ['test'],
    currencies: ['test'],
    meta: {
      created: null,
      form: '',
      id: 1,
      updated: null,
    },
  },
  {
    code: 'mobile',
    labels: {en_US: 'Mobile'},
    locales: locales,
    category_tree: '1',
    conversion_units: ['test'],
    currencies: ['test'],
    meta: {
      created: null,
      form: '',
      id: 1,
      updated: null,
    },
  },
];

test('It renders the current channel', () => {
  renderWithProviders(<ChannelSelector values={channels} value={'mobile'} onChange={() => {}} />);

  expect(screen.getByText(/pim_common.channel/)).toBeInTheDocument();
  expect(screen.getByText(/Mobile/)).toBeInTheDocument();
});

test('It renders with an unknown channel', async () => {
  renderWithProviders(<ChannelSelector values={channels} value={'unknown_channel'} onChange={() => {}} />);

  expect(screen.getByText(/pim_common.channel/)).toBeInTheDocument();
  expect(screen.getByTestId(`ChannelSelector.selection`)).toBeEmptyDOMElement();
});

test('It calls onChange handler when user click on another channel', async () => {
  const onChange = jest.fn();

  renderWithProviders(<ChannelSelector values={channels} value={'ecommerce'} onChange={onChange} />);

  userEvent.click(screen.getByText(/Ecommerce/));
  userEvent.click(screen.getByText(/Mobile/));

  expect(onChange).toBeCalledWith('mobile');
});
