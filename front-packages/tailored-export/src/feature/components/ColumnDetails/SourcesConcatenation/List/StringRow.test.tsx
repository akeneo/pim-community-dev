import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from 'feature/tests';
import {ConcatElement} from 'feature';
import {StringRow} from './StringRow';
import {ValidationError} from '@akeneo-pim-community/shared';

const concatElement: ConcatElement = {
  type: 'string',
  uuid: 'string-1e40-4c55-a415-89c7958b270d',
  value: '',
};

test('it calls the change handler when typing in the input', async () => {
  const handleChange = jest.fn();

  await renderWithProviders(
    <table>
      <tbody>
        <StringRow
          validationErrors={[]}
          concatElement={concatElement}
          onConcatElementChange={handleChange}
          onConcatElementRemove={jest.fn()}
        />
      </tbody>
    </table>
  );

  userEvent.type(
    screen.getByPlaceholderText('akeneo.tailored_export.column_details.concatenation.text_placeholder'),
    'f'
  );

  expect(handleChange).toHaveBeenCalledWith({...concatElement, value: 'f'});
});

test('it calls the remove handler when clicking on the remove button', async () => {
  const handleRemove = jest.fn();

  await renderWithProviders(
    <table>
      <tbody>
        <StringRow
          validationErrors={[]}
          concatElement={concatElement}
          onConcatElementChange={jest.fn()}
          onConcatElementRemove={handleRemove}
        />
      </tbody>
    </table>
  );

  userEvent.click(screen.getByTitle('pim_common.remove'));

  expect(handleRemove).toHaveBeenCalledWith(concatElement.uuid);
});

test('it displays value validation errors', async () => {
  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.value',
      parameters: {},
      message: 'invalid value',
      propertyPath: '[value]',
      invalidValue: [],
    },
  ];

  await renderWithProviders(
    <table>
      <tbody>
        <StringRow
          validationErrors={validationErrors}
          concatElement={concatElement}
          onConcatElementChange={jest.fn()}
          onConcatElementRemove={jest.fn()}
        />
      </tbody>
    </table>
  );

  expect(screen.getByText('error.key.value')).toBeInTheDocument();
});
