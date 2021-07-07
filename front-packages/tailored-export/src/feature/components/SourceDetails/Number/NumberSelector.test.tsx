import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders as baseRender, ValidationError} from '@akeneo-pim-community/shared';
import {NumberSelector} from './NumberSelector';

test('it displays a separator dropdown', async () => {
  const onSelectionChange = jest.fn();

  await baseRender(
    <NumberSelector validationErrors={[]} selection={{separator: ','}} onSelectionChange={onSelectionChange} />
  );

  expect(
    screen.getByText('akeneo.tailored_export.column_details.sources.selection.collection_separator')
  ).toBeInTheDocument();
});

test('it can change the separator type', async () => {
  const onSelectionChange = jest.fn();

  await baseRender(
    <NumberSelector validationErrors={[]} selection={{separator: ','}} onSelectionChange={onSelectionChange} />
  );

  userEvent.click(screen.getByText('akeneo.tailored_export.column_details.sources.selection.collection_separator'));
  userEvent.click(screen.getByTitle('.'));

  expect(onSelectionChange).toHaveBeenCalledWith({separator: '.'});
});

test('it displays validation errors', async () => {
  const onSelectionChange = jest.fn();
  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.separator',
      invalidValue: '',
      message: 'this is a separator error',
      parameters: {},
      propertyPath: '[separator]',
    },
  ];

  await baseRender(
    <NumberSelector
      validationErrors={validationErrors}
      selection={{separator: ','}}
      onSelectionChange={onSelectionChange}
    />
  );

  expect(screen.getByText('error.key.separator')).toBeInTheDocument();
});
