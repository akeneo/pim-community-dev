import React from 'react';
import {act, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from 'feature/tests';
import {SimpleSelectReplacement} from './SimpleSelectReplacement';
import {ValidationError} from '@akeneo-pim-community/shared';

const attribute = {
  code: 'simpleselect',
  type: 'pim_catalog_simpleselect',
  labels: {},
  scopable: false,
  localizable: false,
  is_locale_specific: false,
  available_locales: [],
};

test('it can open a replacement modal and calls the handler when confirming', async () => {
  const handleChange = jest.fn();
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => {},
  }));

  await renderWithProviders(
    <SimpleSelectReplacement attribute={attribute} validationErrors={[]} onOperationChange={handleChange} />
  );

  userEvent.click(screen.getByText('akeneo.tailored_export.column_details.sources.operation.replacement.edit_mapping'));

  expect(
    screen.getByText('akeneo.tailored_export.column_details.sources.operation.replacement.modal.title')
  ).toBeInTheDocument();

  await act(async () => {
    await userEvent.click(screen.getByText('pim_common.confirm'));
  });

  expect(handleChange).toHaveBeenCalledWith(undefined);
});

test('it displays validation errors', () => {
  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.blue',
      invalidValue: '',
      message: 'this is a blue error',
      parameters: {},
      propertyPath: '[mapping][blue]',
    },
    {
      messageTemplate: 'error.key.black',
      invalidValue: '',
      message: 'this is a black error',
      parameters: {},
      propertyPath: '[mapping][black]',
    },
  ];

  renderWithProviders(
    <SimpleSelectReplacement attribute={attribute} validationErrors={validationErrors} onOperationChange={jest.fn()} />
  );

  expect(screen.getByRole('alert')).toBeInTheDocument();
});
