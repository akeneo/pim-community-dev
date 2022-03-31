import {renderWithProviders} from '../../../tests';
import {act, screen} from '@testing-library/react';
import React from 'react';
import {createAttributeTarget, createPropertyTarget} from '../../../models';
import {MeasurementConfigurator} from './MeasurementConfigurator';
import userEvent from '@testing-library/user-event';
import {ValidationError} from '@akeneo-pim-community/shared/lib/models/validation-error';

const flushPromises = () => new Promise(setImmediate);

const getMeasurementAttribute = (withDecimal: boolean) => ({
  code: 'weight',
  type: 'pim_catalog_metric',
  labels: {},
  scopable: false,
  localizable: false,
  is_locale_specific: false,
  available_locales: [],
  decimals_allowed: withDecimal,
  metric_family: 'Weight',
  default_metric_unit: 'kilogram',
});

test('it does not display a decimal separator selector if attribute does not support decimal numbers', async () => {
  const attribute = getMeasurementAttribute(false);
  const target = createAttributeTarget(attribute, null, null);

  await renderWithProviders(
    <MeasurementConfigurator
      target={target}
      attribute={attribute}
      validationErrors={[]}
      onTargetAttributeChange={jest.fn()}
    />
  );

  expect(
    screen.queryByText('akeneo.tailored_import.data_mapping.target.parameters.decimal_separator.title')
  ).not.toBeInTheDocument();
});

test('it displays a decimal separator selector if attribute supports decimal numbers', async () => {
  const attribute = getMeasurementAttribute(true);
  const target = createAttributeTarget(attribute, null, null);

  await renderWithProviders(
    <MeasurementConfigurator
      target={target}
      attribute={attribute}
      validationErrors={[]}
      onTargetAttributeChange={jest.fn()}
    />
  );

  expect(
    screen.queryByText('akeneo.tailored_import.data_mapping.target.parameters.decimal_separator.title')
  ).toBeInTheDocument();
});

test('it displays all decimal separators when opening the dropdown', async () => {
  const attribute = getMeasurementAttribute(true);
  const target = createAttributeTarget(attribute, null, null);

  await renderWithProviders(
    <MeasurementConfigurator
      target={target}
      attribute={attribute}
      validationErrors={[]}
      onTargetAttributeChange={jest.fn()}
    />
  );

  userEvent.click(
    screen.getByLabelText('akeneo.tailored_import.data_mapping.target.parameters.decimal_separator.title')
  );

  expect(
    screen.getAllByText('akeneo.tailored_import.data_mapping.target.parameters.decimal_separator.dot')
  ).toHaveLength(2);
  expect(
    screen.getByText('akeneo.tailored_import.data_mapping.target.parameters.decimal_separator.comma')
  ).toBeInTheDocument();
  expect(
    screen.getByText('akeneo.tailored_import.data_mapping.target.parameters.decimal_separator.arabic_comma')
  ).toBeInTheDocument();
});

test('it defines the decimal separator of the target', async () => {
  const attribute = getMeasurementAttribute(true);
  const target = createAttributeTarget(attribute, null, null);
  const onTargetChange = jest.fn();

  await renderWithProviders(
    <MeasurementConfigurator
      target={target}
      attribute={attribute}
      validationErrors={[]}
      onTargetAttributeChange={onTargetChange}
    />
  );

  userEvent.click(
    screen.getByLabelText('akeneo.tailored_import.data_mapping.target.parameters.decimal_separator.title')
  );
  userEvent.click(screen.getByText('akeneo.tailored_import.data_mapping.target.parameters.decimal_separator.comma'));

  expect(onTargetChange).toHaveBeenCalledWith({
    ...target,
    source_parameter: {
      decimal_separator: ',',
      unit: 'kilogram',
    },
  });
});

test('it throws an error if we setup this component with a wrong target', async () => {
  const attribute = getMeasurementAttribute(true);
  const target = createPropertyTarget('family');

  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  await expect(async () => {
    await renderWithProviders(
      <MeasurementConfigurator
        target={target}
        attribute={attribute}
        validationErrors={[]}
        onTargetAttributeChange={jest.fn()}
      />
    );
  }).rejects.toThrow('Invalid target data "family" for measurement configurator');

  mockedConsole.mockRestore();
});

test('it should display a measurement unit selector', async () => {
  const attribute = getMeasurementAttribute(false);
  const target = createAttributeTarget(attribute, null, null);

  await renderWithProviders(
    <MeasurementConfigurator
      target={target}
      attribute={attribute}
      validationErrors={[]}
      onTargetAttributeChange={jest.fn()}
    />
  );

  await act(async () => {
    await flushPromises();
  });

  expect(
    screen.getByLabelText('akeneo.tailored_import.data_mapping.target.parameters.measurement_unit.title')
  ).toBeInTheDocument();
});

test('it defines the measurement unit of the target', async () => {
  const attribute = getMeasurementAttribute(false);
  const target = createAttributeTarget(attribute, null, null);
  const onTargetChange = jest.fn();

  await renderWithProviders(
    <MeasurementConfigurator
      target={target}
      attribute={attribute}
      validationErrors={[]}
      onTargetAttributeChange={onTargetChange}
    />
  );

  await act(async () => {
    await flushPromises();
  });

  userEvent.click(
    screen.getByLabelText('akeneo.tailored_import.data_mapping.target.parameters.measurement_unit.title')
  );
  userEvent.click(screen.getByText('Gram'));

  expect(onTargetChange).toHaveBeenCalledWith({
    ...target,
    source_parameter: {
      decimal_separator: '.',
      unit: 'gram',
    },
  });
});

test('it throws an error if we setup this component with an attribute without metric_family', async () => {
  let attribute = getMeasurementAttribute(false);
  attribute.metric_family = '';

  const target = createAttributeTarget(attribute, null, null);

  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  await expect(async () => {
    await renderWithProviders(
      <MeasurementConfigurator
        target={target}
        attribute={attribute}
        validationErrors={[]}
        onTargetAttributeChange={jest.fn()}
      />
    );
  }).rejects.toThrow('Invalid metric family for measurement configurator');

  mockedConsole.mockRestore();
});

test('it should display helper if there are validation errors', async () => {
  const attribute = getMeasurementAttribute(true);
  const target = createAttributeTarget(attribute, null, null);
  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.global',
      invalidValue: '',
      message: 'this is a global error',
      parameters: {},
      propertyPath: '',
    },
    {
      messageTemplate: 'error.key.unit',
      invalidValue: 'FOO',
      message: 'this is a measurement unit error',
      parameters: {},
      propertyPath: '[unit]',
    },
    {
      messageTemplate: 'error.key.decimal_separator',
      invalidValue: '#',
      message: 'this is a decimal separator error',
      parameters: {},
      propertyPath: '[decimal_separator]',
    },
  ];

  await renderWithProviders(
    <MeasurementConfigurator
      target={target}
      attribute={attribute}
      validationErrors={validationErrors}
      onTargetAttributeChange={jest.fn()}
    />
  );

  expect(screen.getByText('error.key.decimal_separator')).toBeInTheDocument();
  expect(screen.getByText('error.key.unit')).toBeInTheDocument();
  expect(screen.queryByText('error.key.global')).not.toBeInTheDocument();
});
