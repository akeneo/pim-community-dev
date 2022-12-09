import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {ValidationError} from '@akeneo-pim-community/shared';
import {channels, renderWithProviders} from 'feature/tests';
import {ReferenceEntityAttribute} from 'feature/models';
import {ReferenceEntityNumberAttributeSelection} from '../model';
import {NumberAttributeSelector} from './NumberAttributeSelector';

const attribute: ReferenceEntityAttribute = {
  code: 'size',
  identifier: 'size_1234',
  type: 'number',
  labels: {},
  value_per_channel: false,
  value_per_locale: false,
};

const selection: ReferenceEntityNumberAttributeSelection = {
  type: 'attribute',
  attribute_identifier: 'size_1234',
  attribute_type: 'number',
  reference_entity_code: 'designer',
  channel: null,
  locale: null,
  decimal_separator: ',',
};

test('it can change the decimal separator', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <NumberAttributeSelector
      attribute={attribute}
      selection={selection}
      channels={channels}
      validationErrors={[]}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(screen.getByText('akeneo.tailored_export.column_details.sources.selection.decimal_separator.title'));
  userEvent.click(screen.getByTitle('akeneo.tailored_export.column_details.sources.selection.decimal_separator.dot'));

  expect(onSelectionChange).toHaveBeenCalledWith({
    ...selection,
    decimal_separator: '.',
  });
});

test('it throws when the selection is invalid', async () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const invalidSelection = {...selection, attribute_type: 'image'};

  await expect(async () => {
    await renderWithProviders(
      <NumberAttributeSelector
        attribute={attribute}
        selection={invalidSelection}
        channels={channels}
        validationErrors={[]}
        onSelectionChange={jest.fn()}
      />
    );
  }).rejects.toThrow('Invalid selection type for Number Attribute Selector');

  mockedConsole.mockRestore();
});

test('it displays validation errors', async () => {
  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.decimal_separator',
      invalidValue: '',
      message: 'this is a decimal separator error',
      parameters: {},
      propertyPath: '[decimal_separator]',
    },
  ];

  await renderWithProviders(
    <NumberAttributeSelector
      attribute={attribute}
      selection={selection}
      channels={channels}
      validationErrors={validationErrors}
      onSelectionChange={jest.fn()}
    />
  );

  expect(screen.getByText('error.key.decimal_separator')).toBeInTheDocument();
});
