import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {SearchAndReplaceValue} from './SearchAndReplaceOperationBlock';
import {SearchAndReplaceValueRow} from './SearchAndReplaceValueRow';

const replacement: SearchAndReplaceValue = {
  uuid: expect.any(String),
  what: 'replace m',
  with: '',
  case_sensitive: false,
};

test('it renders a search and replace row and handles changes', () => {
  const handleChange = jest.fn();

  renderWithProviders(
    <table>
      <tbody>
        <SearchAndReplaceValueRow replacement={replacement} validationErrors={[]} onReplacementChange={handleChange} />
      </tbody>
    </table>
  );

  userEvent.type(
    screen.getByPlaceholderText('akeneo.tailored_import.data_mapping.operations.search_and_replace.what.placeholder'),
    'e'
  );
  expect(handleChange).toHaveBeenCalledWith({...replacement, what: 'replace me'});

  userEvent.type(
    screen.getByPlaceholderText('akeneo.tailored_import.data_mapping.operations.search_and_replace.with.placeholder'),
    'w'
  );
  expect(handleChange).toHaveBeenCalledWith({...replacement, with: 'w'});

  userEvent.click(screen.getByRole('checkbox'));
  expect(handleChange).toHaveBeenCalledWith({...replacement, case_sensitive: true});
});

test('it displays validation errors', () => {
  renderWithProviders(
    <table>
      <tbody>
        <SearchAndReplaceValueRow
          replacement={replacement}
          validationErrors={[
            {
              messageTemplate: 'error.key.what_error',
              invalidValue: '',
              message: 'this is a what error',
              parameters: {},
              propertyPath: '[what]',
            },
            {
              messageTemplate: 'error.key.with_error',
              invalidValue: '',
              message: 'this is a with error',
              parameters: {},
              propertyPath: '[with]',
            },
          ]}
          onReplacementChange={jest.fn()}
        />
      </tbody>
    </table>
  );

  expect(screen.getByText('error.key.what_error')).toBeInTheDocument();
  expect(screen.getByText('error.key.with_error')).toBeInTheDocument();
});
