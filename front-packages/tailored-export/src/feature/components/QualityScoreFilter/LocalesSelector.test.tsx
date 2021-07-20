import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {LocalesSelector} from './LocalesSelector';
import React from 'react';
import userEvent from '@testing-library/user-event';

const availableLocales = [
  {
    code: 'en_US',
    label: 'English (American)',
    region: 'US',
    language: 'en',
  },
  {
    code: 'fr_FR',
    label: 'French',
    region: 'FR',
    language: 'fr',
  },
];

test('it displays the selected locales', () => {
  renderWithProviders(
    <LocalesSelector value={['en_US']} locales={availableLocales} onChange={() => {}} validationErrors={[]} />
  );

  expect(screen.queryByText('akeneo.tailored_export.filters.quality_score.locales.label')).toBeInTheDocument();
  expect(screen.queryByText('English (American)')).toBeInTheDocument();
});

test('it notifies when a locale is added to the selection', () => {
  const onLocalesSelectionChange = jest.fn();
  renderWithProviders(
    <LocalesSelector
      value={['fr_FR']}
      locales={availableLocales}
      onChange={onLocalesSelectionChange}
      validationErrors={[]}
    />
  );

  userEvent.click(screen.getByText('akeneo.tailored_export.filters.quality_score.locales.label'));
  userEvent.click(screen.getByText('English (American)'));

  expect(onLocalesSelectionChange).toHaveBeenCalledWith(['fr_FR', 'en_US']);
});

test('it displays validations errors if any', () => {
  const myErrorMessage = 'My message.';

  renderWithProviders(
    <LocalesSelector
      value={['fr_FR']}
      locales={availableLocales}
      onChange={() => {}}
      validationErrors={[
        {
          messageTemplate: myErrorMessage,
          parameters: {},
          message: myErrorMessage,
          propertyPath: '',
          invalidValue: '',
        },
      ]}
    />
  );

  expect(screen.queryByText(myErrorMessage)).toBeInTheDocument();
});
