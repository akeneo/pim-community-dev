import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders, ValidationError} from '@akeneo-pim-community/shared';
import {FileSelector} from './FileSelector';

test('it can select a key selection type', () => {
  const onSelectionChange = jest.fn();

  renderWithProviders(
    <FileSelector selection={{type: 'path'}} validationErrors={[]} onSelectionChange={onSelectionChange} />
  );

  userEvent.click(screen.getByLabelText('pim_common.type'));
  userEvent.click(screen.getByTitle('akeneo.tailored_export.column_details.sources.selection.type.key'));

  expect(onSelectionChange).toHaveBeenCalledWith({type: 'key'});
});

test('it can select a name selection type', () => {
  const onSelectionChange = jest.fn();

  renderWithProviders(
    <FileSelector selection={{type: 'key'}} validationErrors={[]} onSelectionChange={onSelectionChange} />
  );

  userEvent.click(screen.getByLabelText('pim_common.type'));
  userEvent.click(screen.getByTitle('akeneo.tailored_export.column_details.sources.selection.type.name'));

  expect(onSelectionChange).toHaveBeenCalledWith({type: 'name'});
});

test('it displays validation errors', () => {
  const onSelectionChange = jest.fn();
  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.type',
      invalidValue: '',
      message: 'this is a type error',
      parameters: {},
      propertyPath: '[type]',
    },
  ];

  renderWithProviders(
    <FileSelector
      validationErrors={validationErrors}
      selection={{type: 'path'}}
      onSelectionChange={onSelectionChange}
    />
  );

  expect(screen.getByText('error.key.type')).toBeInTheDocument();
});
