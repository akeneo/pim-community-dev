import React from 'react';
import {act, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from 'feature/tests';
import {SimpleSelectReplacement} from './SimpleSelectReplacement';
import {ValidationError} from '@akeneo-pim-community/shared';

jest.mock('../../../hooks/useAttributeOptions', () => ({
  useAttributeOptions: (
    _attributeCode: string,
    searchValue: string,
    _page: number,
    includeCodes: string[],
    excludeCodes: string[]
  ) => [
    [
      {
        code: 'black',
        labels: {
          en_US: 'Black',
        },
      },
      {
        code: 'red',
        labels: {
          en_US: 'Red',
        },
      },
      {
        code: 'blue',
        labels: {
          en_US: 'Blue',
        },
      },
    ].filter(
      ({code}) =>
        code.includes(searchValue) &&
        (null === includeCodes || includeCodes.includes(code)) &&
        (null === excludeCodes || !excludeCodes.includes(code))
    ),
    3,
  ],
}));

test('it can open a replacement modal and calls the handler when confirming', async () => {
  const handleChange = jest.fn();
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => {},
  }));

  await renderWithProviders(
    <SimpleSelectReplacement attributeCode="simpleselect" validationErrors={[]} onOperationChange={handleChange} />
  );

  userEvent.click(screen.getByText('akeneo.tailored_export.column_details.sources.operation.replacement.edit_mapping'));

  expect(
    screen.getByText('akeneo.tailored_export.column_details.sources.operation.replacement.modal.title')
  ).toBeInTheDocument();

  await act(async () => {
    userEvent.click(screen.getByText('pim_common.confirm'));
  });

  expect(handleChange).toHaveBeenCalledWith(undefined);
});

test('it does not change replacement when cancelling', async () => {
  const handleChange = jest.fn();

  await renderWithProviders(
    <SimpleSelectReplacement attributeCode="color" validationErrors={[]} onOperationChange={handleChange} />
  );

  userEvent.click(screen.getByText('akeneo.tailored_export.column_details.sources.operation.replacement.edit_mapping'));

  expect(
    screen.getByText('akeneo.tailored_export.column_details.sources.operation.replacement.modal.title')
  ).toBeInTheDocument();

  userEvent.click(screen.getByTitle('pim_common.close'));

  expect(handleChange).not.toHaveBeenCalled();
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
    <SimpleSelectReplacement
      attributeCode="simpleselect"
      validationErrors={validationErrors}
      onOperationChange={jest.fn()}
    />
  );

  expect(screen.getByRole('alert')).toBeInTheDocument();
});
