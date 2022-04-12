import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {ValidationError} from '@akeneo-pim-community/shared';
import {renderWithProviders} from 'feature/tests';
import {createAttributeDataMapping, createPropertyDataMapping} from '../../../../models';
import {NumberConfigurator} from './NumberConfigurator';

jest.mock('../../Operations');
jest.mock('../../Sources');
jest.mock('../../AttributeTargetParameters');

const getNumberAttributeWithDecimal = () => ({
  code: 'alcohol_percentage',
  type: 'pim_catalog_number',
  labels: {},
  scopable: false,
  localizable: false,
  is_locale_specific: false,
  available_locales: [],
  decimals_allowed: true,
});

test('it does not display a decimal separator selector if attribute does not support decimal numbers', async () => {
  const numberAttribute = {
    code: 'response_time',
    type: 'pim_catalog_number',
    labels: {},
    scopable: false,
    localizable: false,
    is_locale_specific: false,
    available_locales: [],
    decimals_allowed: false,
  };

  const dataMapping = createAttributeDataMapping(numberAttribute, []);

  await renderWithProviders(
    <NumberConfigurator
      columns={[]}
      dataMapping={dataMapping}
      onRefreshSampleData={jest.fn()}
      onOperationsChange={jest.fn()}
      onTargetChange={jest.fn()}
      onSourcesChange={jest.fn()}
      attribute={numberAttribute}
      validationErrors={[]}
    />
  );

  expect(
    screen.queryByText('akeneo.tailored_import.data_mapping.target.parameters.decimal_separator.title')
  ).not.toBeInTheDocument();
});

test('it displays a decimal separator selector if attribute supports decimal numbers', async () => {
  const numberAttribute = getNumberAttributeWithDecimal();
  const dataMapping = createAttributeDataMapping(numberAttribute, []);

  await renderWithProviders(
    <NumberConfigurator
      columns={[]}
      dataMapping={dataMapping}
      onRefreshSampleData={jest.fn()}
      onOperationsChange={jest.fn()}
      onTargetChange={jest.fn()}
      onSourcesChange={jest.fn()}
      attribute={numberAttribute}
      validationErrors={[]}
    />
  );

  expect(
    screen.queryByText('akeneo.tailored_import.data_mapping.target.parameters.decimal_separator.title')
  ).toBeInTheDocument();
});

test('it displays all decimal separators when opening the dropdown', async () => {
  const numberAttribute = getNumberAttributeWithDecimal();
  const dataMapping = createAttributeDataMapping(numberAttribute, []);

  await renderWithProviders(
    <NumberConfigurator
      columns={[]}
      dataMapping={dataMapping}
      onRefreshSampleData={jest.fn()}
      onOperationsChange={jest.fn()}
      onTargetChange={jest.fn()}
      onSourcesChange={jest.fn()}
      attribute={numberAttribute}
      validationErrors={[]}
    />
  );

  userEvent.click(screen.getByTitle('pim_common.open'));

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
  const numberAttribute = getNumberAttributeWithDecimal();
  const dataMapping = createAttributeDataMapping(numberAttribute, []);
  const onTargetChange = jest.fn();

  await renderWithProviders(
    <NumberConfigurator
      columns={[]}
      dataMapping={dataMapping}
      onRefreshSampleData={jest.fn()}
      onOperationsChange={jest.fn()}
      onTargetChange={onTargetChange}
      onSourcesChange={jest.fn()}
      attribute={numberAttribute}
      validationErrors={[]}
    />
  );

  userEvent.click(screen.getByTitle('pim_common.open'));
  userEvent.click(screen.getByText('akeneo.tailored_import.data_mapping.target.parameters.decimal_separator.comma'));

  expect(onTargetChange).toHaveBeenCalledWith({
    ...dataMapping.target,
    source_parameter: {
      decimal_separator: ',',
    },
  });
});

test('it defines if the attribute should be cleared when empty', async () => {
  const attribute = getNumberAttributeWithDecimal();
  const dataMapping = createAttributeDataMapping(attribute, []);
  const onTargetChange = jest.fn();

  await renderWithProviders(
    <NumberConfigurator
      columns={[]}
      dataMapping={dataMapping}
      onRefreshSampleData={jest.fn()}
      onOperationsChange={jest.fn()}
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
  const numberAttribute = getNumberAttributeWithDecimal();
  const dataMapping = createPropertyDataMapping('family');

  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  await expect(async () => {
    await renderWithProviders(
      <NumberConfigurator
        columns={[]}
        // @ts-expect-error invalid data mapping type
        dataMapping={dataMapping}
        onRefreshSampleData={jest.fn()}
        onTargetChange={jest.fn()}
        onSourcesChange={jest.fn()}
        attribute={numberAttribute}
        validationErrors={[]}
      />
    );
  }).rejects.toThrow('Invalid target data "family" for number configurator');

  mockedConsole.mockRestore();
});

test('it should display validation errors', async () => {
  const attribute = getNumberAttributeWithDecimal();
  const dataMapping = createAttributeDataMapping(attribute, []);

  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.global',
      invalidValue: '',
      message: 'this is a global error',
      parameters: {},
      propertyPath: '',
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
    <NumberConfigurator
      columns={[]}
      dataMapping={dataMapping}
      onOperationsChange={jest.fn()}
      onRefreshSampleData={jest.fn()}
      onTargetChange={jest.fn()}
      onSourcesChange={jest.fn()}
      attribute={attribute}
      validationErrors={validationErrors}
    />
  );

  expect(screen.getByText('error.key.sources')).toBeInTheDocument();
  expect(screen.getByText('error.key.target')).toBeInTheDocument();
  expect(screen.getByText('error.key.decimal_separator')).toBeInTheDocument();
  expect(screen.queryByText('error.key.global')).not.toBeInTheDocument();
});
