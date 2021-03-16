import React from 'react';
import {act, screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {fireEvent} from '@testing-library/dom';
import {LocaleSelector} from '../../../../Resources/workspaces/shared';

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

test('It renders current locale', () => {
  renderWithProviders(<LocaleSelector values={locales} value={'fr_FR'} />);

  expect(screen.getByText('pim_enrich.entity.locale.plural_label:')).toBeInTheDocument();
  expect(screen.getByText('French (France)')).toBeInTheDocument();
});

test('It triggers callback on change', async () => {
  const onChange = jest.fn();

  renderWithProviders(<LocaleSelector values={locales} value={'fr_FR'} onChange={onChange} />);

  await act(async () => {
    fireEvent.click(screen.getAllByRole('button')[0]);
  });
  await act(async () => {
    fireEvent.click(screen.getByText('English (United States)'));
  });

  expect(onChange).toBeCalledWith('en_US');
});

test('It displays badges for incomplete values', async () => {
  renderWithProviders(<LocaleSelector values={locales} value={'fr_FR'} completeValues={['en_US']} />);

  await act(async () => {
    fireEvent.click(screen.getAllByRole('button')[0]);
  });

  expect(screen.queryByTestId('LocaleSelector.incomplete.en_US')).not.toBeInTheDocument();
  expect(screen.getByTestId('LocaleSelector.incomplete.fr_FR')).toBeInTheDocument();
});
