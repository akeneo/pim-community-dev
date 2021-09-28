import React from 'react';
import {act, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from 'feature/tests';
import {ReferenceEntityReplacement} from './ReferenceEntityReplacement';
import {ValidationError} from '@akeneo-pim-community/shared';

jest.mock('./useRecords', () => ({
  useRecords: (
    _referenceEntityCode: string,
    searchValue: string,
    _page: number,
    includeCodes: string[] | null,
    excludeCodes: string[] | null
  ) => [
    [
      {
        code: 'alessi',
        labels: {
          en_US: 'Alessi',
        },
      },
      {
        code: 'starck',
        labels: {
          en_US: 'Starck',
        },
      },
      {
        code: 'yamaha',
        labels: {
          en_US: 'Yamaha',
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
    <ReferenceEntityReplacement referenceEntityCode="brand" validationErrors={[]} onOperationChange={handleChange} />
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
      messageTemplate: 'error.key.alessi',
      invalidValue: '',
      message: 'this is a alessi error',
      parameters: {},
      propertyPath: '[mapping][alessi]',
    },
  ];

  renderWithProviders(
    <ReferenceEntityReplacement
      referenceEntityCode="brand"
      validationErrors={validationErrors}
      onOperationChange={jest.fn()}
    />
  );

  expect(screen.getByRole('alert')).toBeInTheDocument();
});
