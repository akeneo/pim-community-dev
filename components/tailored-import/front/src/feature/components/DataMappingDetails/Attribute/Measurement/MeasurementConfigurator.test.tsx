import React from 'react';
import {act, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {createAttributeDataMapping, createPropertyDataMapping} from '../../../../models';
import {MeasurementConfigurator} from './MeasurementConfigurator';
import {renderWithProviders} from 'feature/tests';
import {ValidationError} from '@akeneo-pim-community/shared';

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

jest.mock('../../Operations');
jest.mock('../../Sources');
jest.mock('../../AttributeTargetParameters');

test('it does not display a decimal separator selector if attribute does not support decimal numbers', async () => {
  const attribute = getMeasurementAttribute(false);
  const dataMapping = createAttributeDataMapping('weight', attribute, []);

  await renderWithProviders(
    <MeasurementConfigurator
      columns={[]}
      dataMapping={dataMapping}
      onRefreshSampleData={jest.fn()}
      onSourcesChange={jest.fn()}
      onTargetChange={jest.fn()}
      attribute={attribute}
      validationErrors={[]}
    />
  );

  expect(
    screen.queryByText('akeneo.tailored_import.data_mapping.target.parameters.decimal_separator.title')
  ).not.toBeInTheDocument();
});

test('it displays a decimal separator selector if attribute supports decimal numbers', async () => {
  const attribute = getMeasurementAttribute(true);
  const dataMapping = createAttributeDataMapping('weight', attribute, []);

  await renderWithProviders(
    <MeasurementConfigurator
      columns={[]}
      dataMapping={dataMapping}
      onRefreshSampleData={jest.fn()}
      onSourcesChange={jest.fn()}
      onTargetChange={jest.fn()}
      attribute={attribute}
      validationErrors={[]}
    />
  );

  expect(
    screen.queryByText('akeneo.tailored_import.data_mapping.target.parameters.decimal_separator.title')
  ).toBeInTheDocument();
});

test('it displays all decimal separators when opening the dropdown', async () => {
  const attribute = getMeasurementAttribute(true);
  const dataMapping = createAttributeDataMapping('weight', attribute, []);

  await renderWithProviders(
    <MeasurementConfigurator
      columns={[]}
      dataMapping={dataMapping}
      onRefreshSampleData={jest.fn()}
      onSourcesChange={jest.fn()}
      onTargetChange={jest.fn()}
      attribute={attribute}
      validationErrors={[]}
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
  const dataMapping = createAttributeDataMapping('weight', attribute, []);
  const onTargetChange = jest.fn();

  await renderWithProviders(
    <MeasurementConfigurator
      columns={[]}
      dataMapping={dataMapping}
      onRefreshSampleData={jest.fn()}
      onSourcesChange={jest.fn()}
      onTargetChange={onTargetChange}
      attribute={attribute}
      validationErrors={[]}
    />
  );

  userEvent.click(
    screen.getByLabelText('akeneo.tailored_import.data_mapping.target.parameters.decimal_separator.title')
  );
  userEvent.click(screen.getByText('akeneo.tailored_import.data_mapping.target.parameters.decimal_separator.comma'));

  expect(onTargetChange).toHaveBeenCalledWith({
    ...dataMapping.target,
    source_parameter: {
      decimal_separator: ',',
      unit: 'kilogram',
    },
  });
});

test('it defines if the attribute should be cleared when empty', async () => {
  const attribute = getMeasurementAttribute(true);
  const dataMapping = createAttributeDataMapping('weight', attribute, []);
  const onTargetChange = jest.fn();

  await renderWithProviders(
    <MeasurementConfigurator
      columns={[]}
      dataMapping={dataMapping}
      onRefreshSampleData={jest.fn()}
      onSourcesChange={jest.fn()}
      onTargetChange={onTargetChange}
      attribute={attribute}
      validationErrors={[]}
    />
  );

  userEvent.click(screen.getByLabelText('akeneo.tailored_import.data_mapping.target.clear_if_empty'));

  expect(onTargetChange).toHaveBeenCalledWith({
    ...dataMapping.target,
    action_if_empty: 'clear',
  });
});

test('it throws an error if we setup this component with a wrong target', async () => {
  const attribute = getMeasurementAttribute(true);
  const dataMapping = createPropertyDataMapping('family');

  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  await expect(async () => {
    await renderWithProviders(
      <MeasurementConfigurator
        columns={[]}
        // @ts-expect-error invalid data mapping type
        dataMapping={dataMapping}
        onRefreshSampleData={jest.fn()}
        onSourcesChange={jest.fn()}
        onTargetChange={jest.fn()}
        attribute={attribute}
        validationErrors={[]}
      />
    );
  }).rejects.toThrow('Invalid target data "family" for measurement configurator');

  mockedConsole.mockRestore();
});

test('it should display a measurement unit selector', async () => {
  const attribute = getMeasurementAttribute(false);
  const dataMapping = createAttributeDataMapping('weight', attribute, []);

  await renderWithProviders(
    <MeasurementConfigurator
      dataMapping={dataMapping}
      columns={[]}
      onRefreshSampleData={jest.fn()}
      onSourcesChange={jest.fn()}
      onTargetChange={jest.fn()}
      attribute={attribute}
      validationErrors={[]}
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
  const dataMapping = createAttributeDataMapping('weight', attribute, []);
  const onTargetChange = jest.fn();

  await renderWithProviders(
    <MeasurementConfigurator
      columns={[]}
      dataMapping={dataMapping}
      attribute={attribute}
      validationErrors={[]}
      onRefreshSampleData={jest.fn()}
      onSourcesChange={jest.fn()}
      onTargetChange={onTargetChange}
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
    ...dataMapping.target,
    source_parameter: {
      decimal_separator: '.',
      unit: 'gram',
    },
  });
});

test('it throws an error if we setup this component with an attribute without metric_family', async () => {
  let attribute = getMeasurementAttribute(false);
  attribute.metric_family = '';

  const dataMapping = createAttributeDataMapping('weight', attribute, []);

  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  await expect(async () => {
    await renderWithProviders(
      <MeasurementConfigurator
        columns={[]}
        dataMapping={dataMapping}
        attribute={attribute}
        validationErrors={[]}
        onRefreshSampleData={jest.fn()}
        onSourcesChange={jest.fn()}
        onTargetChange={jest.fn()}
      />
    );
  }).rejects.toThrow('Invalid metric family for measurement configurator');

  mockedConsole.mockRestore();
});

test('it should display helper if there are validation errors', async () => {
  const attribute = getMeasurementAttribute(true);
  const dataMapping = createAttributeDataMapping('weight', attribute, []);
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
      propertyPath: '[target][unit]',
    },
    {
      messageTemplate: 'error.key.decimal_separator',
      invalidValue: '#',
      message: 'this is a decimal separator error',
      parameters: {},
      propertyPath: '[target][decimal_separator]',
    },
    {
      messageTemplate: 'error.key.target',
      invalidValue: '#',
      message: 'this is a target error',
      parameters: {},
      propertyPath: '[target]',
    },
    {
      messageTemplate: 'error.key.sources',
      invalidValue: '#',
      message: 'this is an sources error',
      parameters: {},
      propertyPath: '[sources]',
    },
  ];

  await renderWithProviders(
    <MeasurementConfigurator
      columns={[]}
      dataMapping={dataMapping}
      attribute={attribute}
      validationErrors={validationErrors}
      onRefreshSampleData={jest.fn()}
      onSourcesChange={jest.fn()}
      onTargetChange={jest.fn()}
    />
  );

  expect(screen.getByText('error.key.decimal_separator')).toBeInTheDocument();
  expect(screen.getByText('error.key.sources')).toBeInTheDocument();
  expect(screen.getByText('error.key.target')).toBeInTheDocument();
  expect(screen.getByText('error.key.unit')).toBeInTheDocument();
  expect(screen.queryByText('error.key.global')).not.toBeInTheDocument();
});
