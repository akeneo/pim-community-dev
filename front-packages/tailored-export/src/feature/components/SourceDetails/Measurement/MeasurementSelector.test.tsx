import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {ValidationError} from '@akeneo-pim-community/shared';
import {MeasurementSelector} from './MeasurementSelector';
import {renderWithProviders} from 'feature/tests';

test('it displays a type dropdown when the selection type is unit_code', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <MeasurementSelector selection={{type: 'unit_code'}} validationErrors={[]} onSelectionChange={onSelectionChange} />
  );

  expect(screen.getByText('pim_common.type')).toBeInTheDocument();
  expect(
    screen.getByText('akeneo.tailored_export.column_details.sources.selection.measurement.unit_code')
  ).toBeInTheDocument();
});

test('it displays a locale dropdown when the selection type is unit_label', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <MeasurementSelector
      selection={{type: 'unit_label', locale: 'en_US'}}
      validationErrors={[]}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(
    screen.getByLabelText('akeneo.tailored_export.column_details.sources.selection.measurement.unit_locale')
  );
  userEvent.click(screen.getByText('Français'));

  expect(onSelectionChange).toHaveBeenCalledWith({type: 'unit_label', locale: 'fr_FR'});
});

test('it can select unit_label selection type', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <MeasurementSelector selection={{type: 'unit_code'}} validationErrors={[]} onSelectionChange={onSelectionChange} />
  );

  userEvent.click(screen.getByText('pim_common.type'));
  userEvent.click(screen.getByTitle('akeneo.tailored_export.column_details.sources.selection.measurement.unit_label'));

  expect(onSelectionChange).toHaveBeenCalledWith({type: 'unit_label', locale: 'en_US'});
});

test('it can select value selection type', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <MeasurementSelector selection={{type: 'unit_code'}} validationErrors={[]} onSelectionChange={onSelectionChange} />
  );

  expect(
    screen.queryByText('akeneo.tailored_export.column_details.sources.selection.decimal_separator.title')
  ).not.toBeInTheDocument();
  userEvent.click(screen.getByText('pim_common.type'));
  userEvent.click(screen.getByTitle('akeneo.tailored_export.column_details.sources.selection.measurement.value'));

  expect(onSelectionChange).toHaveBeenCalledWith({type: 'value'});
});

test('it can select unit_code selection type', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <MeasurementSelector
      selection={{type: 'unit_label', locale: 'en_US'}}
      validationErrors={[]}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(screen.getByText('pim_common.type'));
  userEvent.click(screen.getByTitle('akeneo.tailored_export.column_details.sources.selection.measurement.unit_code'));

  expect(onSelectionChange).toHaveBeenCalledWith({type: 'unit_code'});
});

test('it can change the decimal separator when the selection type is value', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <MeasurementSelector selection={{type: 'value'}} validationErrors={[]} onSelectionChange={onSelectionChange} />
  );

  userEvent.click(screen.getByText('akeneo.tailored_export.column_details.sources.selection.decimal_separator.title'));
  userEvent.click(screen.getByTitle('akeneo.tailored_export.column_details.sources.selection.decimal_separator.comma'));

  expect(onSelectionChange).toHaveBeenCalledWith({type: 'value', decimal_separator: ','});
});

test('it displays validation errors for label selection', async () => {
  const onSelectionChange = jest.fn();
  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.locale',
      invalidValue: '',
      message: 'this is a locale error',
      parameters: {},
      propertyPath: '[locale]',
    },
    {
      messageTemplate: 'error.key.type',
      invalidValue: '',
      message: 'this is a type error',
      parameters: {},
      propertyPath: '[type]',
    },
  ];

  await renderWithProviders(
    <MeasurementSelector
      validationErrors={validationErrors}
      selection={{type: 'unit_label', locale: 'en_US'}}
      onSelectionChange={onSelectionChange}
    />
  );

  expect(screen.getByText('error.key.locale')).toBeInTheDocument();
  expect(screen.getByText('error.key.type')).toBeInTheDocument();
});

test('it displays validation errors for value selection', async () => {
  const onSelectionChange = jest.fn();
  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.type',
      invalidValue: '',
      message: 'this is a type error',
      parameters: {},
      propertyPath: '[type]',
    },
    {
      messageTemplate: 'error.key.decimal_separator',
      invalidValue: '',
      message: 'this is a decimal separator error',
      parameters: {},
      propertyPath: '[decimal_separator]',
    },
  ];

  await renderWithProviders(
    <MeasurementSelector
      validationErrors={validationErrors}
      selection={{type: 'value', decimal_separator: '.'}}
      onSelectionChange={onSelectionChange}
    />
  );

  expect(screen.getByText('error.key.type')).toBeInTheDocument();
  expect(screen.getByText('error.key.decimal_separator')).toBeInTheDocument();
});
