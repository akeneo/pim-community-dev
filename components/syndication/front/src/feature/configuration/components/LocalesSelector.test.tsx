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
    <LocalesSelector
      value={['en_US']}
      locales={availableLocales}
      onChange={() => {}}
      validationErrors={[]}
      label="locale_selector.label"
      placeholder="locale_selector.placeholder"
      removeLabel="locale_selector.remove_label"
    />
  );

  expect(screen.queryByText('locale_selector.label')).toBeInTheDocument();
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
      label="locale_selector.label"
      placeholder="locale_selector.placeholder"
      removeLabel="locale_selector.remove_label"
    />
  );

  userEvent.click(screen.getByText('locale_selector.label'));
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
      label="locale_selector.label"
      placeholder="locale_selector.placeholder"
      removeLabel="locale_selector.remove_label"
    />
  );

  expect(screen.queryByText(myErrorMessage)).toBeInTheDocument();
});
