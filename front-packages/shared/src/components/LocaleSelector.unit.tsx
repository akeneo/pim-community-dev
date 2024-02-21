import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '../tests';
import {LocaleSelector} from './LocaleSelector';
import userEvent from '@testing-library/user-event';

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

test('It renders the current locale', () => {
  renderWithProviders(<LocaleSelector values={locales} value={'fr_FR'} />);

  expect(screen.getByText('pim_common.locale:')).toBeInTheDocument();
  expect(screen.getByText('French (France)')).toBeInTheDocument();
});

test('It calls onChange handler when user click on another locale', async () => {
  const onChange = jest.fn();

  renderWithProviders(<LocaleSelector values={locales} value={'fr_FR'} onChange={onChange} />);

  userEvent.click(screen.getByText('French (France)'));
  userEvent.click(screen.getByText('English (United States)'));

  expect(onChange).toBeCalledWith('en_US');
});

test('It displays badges for incomplete values', async () => {
  renderWithProviders(<LocaleSelector values={locales} value={'fr_FR'} completeValues={['en_US']} />);

  userEvent.click(screen.getByText('French (France)'));

  expect(screen.queryByTestId('LocaleSelector.incomplete.en_US')).not.toBeInTheDocument();
  expect(screen.getByTestId('LocaleSelector.incomplete.fr_FR')).toBeInTheDocument();
});
