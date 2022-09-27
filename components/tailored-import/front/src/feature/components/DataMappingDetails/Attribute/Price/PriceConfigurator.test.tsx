import React from 'react';
import {act, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {createAttributeDataMapping, createPropertyDataMapping} from '../../../../models';
import {PriceConfigurator} from './PriceConfigurator';
import {renderWithProviders} from 'feature/tests';
import {ValidationError} from '@akeneo-pim-community/shared';

const flushPromises = () => new Promise(setImmediate);

const getPriceAttribute = (withDecimal: boolean) => ({
  code: 'net_price',
  type: 'pim_catalog_price_collection',
  labels: {},
  scopable: false,
  localizable: false,
  is_locale_specific: false,
  available_locales: [],
  decimals_allowed: withDecimal,
});

jest.mock('../../Operations');
jest.mock('../../Sources');
jest.mock('../../AttributeTargetParameters');

test('it does not display a decimal separator selector if attribute does not support decimal numbers', async () => {
  const attribute = getPriceAttribute(false);
  const dataMapping = createAttributeDataMapping(attribute, []);

  await renderWithProviders(
    <PriceConfigurator
      columns={[]}
      dataMapping={dataMapping}
      onRefreshSampleData={jest.fn()}
      onSourcesChange={jest.fn()}
      onOperationsChange={jest.fn()}
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
  const attribute = getPriceAttribute(true);
  const dataMapping = createAttributeDataMapping(attribute, []);

  await renderWithProviders(
    <PriceConfigurator
      columns={[]}
      dataMapping={dataMapping}
      onRefreshSampleData={jest.fn()}
      onSourcesChange={jest.fn()}
      onOperationsChange={jest.fn()}
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
  const attribute = getPriceAttribute(true);
  const dataMapping = createAttributeDataMapping(attribute, []);

  await renderWithProviders(
    <PriceConfigurator
      columns={[]}
      dataMapping={dataMapping}
      onRefreshSampleData={jest.fn()}
      onSourcesChange={jest.fn()}
      onOperationsChange={jest.fn()}
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
  const attribute = getPriceAttribute(true);
  const dataMapping = createAttributeDataMapping(attribute, []);
  const onTargetChange = jest.fn();

  await renderWithProviders(
    <PriceConfigurator
      columns={[]}
      dataMapping={dataMapping}
      onRefreshSampleData={jest.fn()}
      onSourcesChange={jest.fn()}
      onOperationsChange={jest.fn()}
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
    source_configuration: {
      decimal_separator: ',',
      currency: null,
    },
  });
});

test('it defines if the attribute should be cleared when empty', async () => {
  const attribute = getPriceAttribute(true);
  const dataMapping = createAttributeDataMapping(attribute, []);
  const onTargetChange = jest.fn();

  await renderWithProviders(
    <PriceConfigurator
      columns={[]}
      dataMapping={dataMapping}
      onRefreshSampleData={jest.fn()}
      onSourcesChange={jest.fn()}
      onOperationsChange={jest.fn()}
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
  const attribute = getPriceAttribute(true);
  const dataMapping = createPropertyDataMapping('family');

  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  await expect(async () => {
    await renderWithProviders(
      <PriceConfigurator
        columns={[]}
        // @ts-expect-error invalid data mapping type
        dataMapping={dataMapping}
        onRefreshSampleData={jest.fn()}
        onSourcesChange={jest.fn()}
        onOperationsChange={jest.fn()}
        onTargetChange={jest.fn()}
        attribute={attribute}
        validationErrors={[]}
      />
    );
  }).rejects.toThrow('Invalid target data "family" for price configurator');

  mockedConsole.mockRestore();
});

test('it should display a price currency selector', async () => {
  const attribute = getPriceAttribute(false);
  const dataMapping = createAttributeDataMapping(attribute, []);

  await renderWithProviders(
    <PriceConfigurator
      dataMapping={dataMapping}
      columns={[]}
      onRefreshSampleData={jest.fn()}
      onSourcesChange={jest.fn()}
      onOperationsChange={jest.fn()}
      onTargetChange={jest.fn()}
      attribute={attribute}
      validationErrors={[]}
    />
  );

  await act(async () => {
    await flushPromises();
  });

  expect(
    screen.getByLabelText('akeneo.tailored_import.data_mapping.target.parameters.price_currency.title')
  ).toBeInTheDocument();
});

test('it defines the price currency of the target', async () => {
  const attribute = getPriceAttribute(false);
  const dataMapping = createAttributeDataMapping(attribute, []);
  const onTargetChange = jest.fn();

  await renderWithProviders(
    <PriceConfigurator
      columns={[]}
      dataMapping={dataMapping}
      attribute={attribute}
      validationErrors={[]}
      onRefreshSampleData={jest.fn()}
      onSourcesChange={jest.fn()}
      onOperationsChange={jest.fn()}
      onTargetChange={onTargetChange}
    />
  );

  await act(async () => {
    await flushPromises();
  });

  userEvent.click(screen.getByLabelText('akeneo.tailored_import.data_mapping.target.parameters.price_currency.title'));
  userEvent.click(screen.getByText('USD'));

  expect(onTargetChange).toHaveBeenCalledWith({
    ...dataMapping.target,
    source_configuration: {
      decimal_separator: '.',
      currency: 'USD',
    },
  });
});

test('it should display helper if there are validation errors', async () => {
  const attribute = getPriceAttribute(true);
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
      messageTemplate: 'error.key.currency',
      invalidValue: 'FOO',
      message: 'this is a price currency error',
      parameters: {},
      propertyPath: '[target][source_configuration][currency]',
    },
    {
      messageTemplate: 'error.key.decimal_separator',
      invalidValue: '#',
      message: 'this is a decimal separator error',
      parameters: {},
      propertyPath: '[target][source_configuration][decimal_separator]',
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
    <PriceConfigurator
      columns={[]}
      dataMapping={dataMapping}
      attribute={attribute}
      validationErrors={validationErrors}
      onRefreshSampleData={jest.fn()}
      onSourcesChange={jest.fn()}
      onOperationsChange={jest.fn()}
      onTargetChange={jest.fn()}
    />
  );

  expect(screen.getByText('error.key.decimal_separator')).toBeInTheDocument();
  expect(screen.getByText('error.key.sources')).toBeInTheDocument();
  expect(screen.getByText('error.key.target')).toBeInTheDocument();
  expect(screen.getByText('error.key.currency')).toBeInTheDocument();
  expect(screen.queryByText('error.key.global')).not.toBeInTheDocument();
});
