import React from 'react';
import {NumberConfigurator} from './NumberConfigurator';
import {createAttributeTarget, createPropertyTarget} from '../../../models';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {ValidationError} from '@akeneo-pim-community/shared';
import {renderWithProviders} from 'feature/tests';

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

  const target = createAttributeTarget(numberAttribute, null, null);

  await renderWithProviders(
    <NumberConfigurator
      target={target}
      attribute={numberAttribute}
      validationErrors={[]}
      onTargetAttributeChange={jest.fn()}
    />
  );

  expect(
    screen.queryByText('akeneo.tailored_import.data_mapping.target.parameters.decimal_separator.title')
  ).not.toBeInTheDocument();
});

test('it displays a decimal separator selector if attribute supports decimal numbers', async () => {
  const numberAttribute = getNumberAttributeWithDecimal();
  const target = createAttributeTarget(numberAttribute, null, null);

  await renderWithProviders(
    <NumberConfigurator
      target={target}
      attribute={numberAttribute}
      validationErrors={[]}
      onTargetAttributeChange={jest.fn()}
    />
  );

  expect(
    screen.queryByText('akeneo.tailored_import.data_mapping.target.parameters.decimal_separator.title')
  ).toBeInTheDocument();
});

test('it displays all decimal separators when opening the dropdown', async () => {
  const numberAttribute = getNumberAttributeWithDecimal();
  const target = createAttributeTarget(numberAttribute, null, null);

  await renderWithProviders(
    <NumberConfigurator
      target={target}
      attribute={numberAttribute}
      validationErrors={[]}
      onTargetAttributeChange={jest.fn()}
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
  const target = createAttributeTarget(numberAttribute, null, null);
  const onTargetChange = jest.fn();

  await renderWithProviders(
    <NumberConfigurator
      target={target}
      attribute={numberAttribute}
      validationErrors={[]}
      onTargetAttributeChange={onTargetChange}
    />
  );

  userEvent.click(screen.getByTitle('pim_common.open'));
  userEvent.click(screen.getByText('akeneo.tailored_import.data_mapping.target.parameters.decimal_separator.comma'));

  expect(onTargetChange).toHaveBeenCalledWith({
    ...target,
    source_parameter: {
      decimal_separator: ',',
    },
  });
});

test('it throws an error if we setup this component with a wrong target', async () => {
  const numberAttribute = getNumberAttributeWithDecimal();
  const target = createPropertyTarget('family');

  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  await expect(async () => {
    await renderWithProviders(
      <NumberConfigurator
        target={target}
        attribute={numberAttribute}
        validationErrors={[]}
        onTargetAttributeChange={jest.fn()}
      />
    );
  }).rejects.toThrow('Invalid target data "family" for number configurator');

  mockedConsole.mockRestore();
});

test('it should display helper if there is decimal separator errors', async () => {
  const numberAttribute = getNumberAttributeWithDecimal();
  const target = createAttributeTarget(numberAttribute, null, null);
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
      propertyPath: '[decimal_separator]',
    },
  ];

  await renderWithProviders(
    <NumberConfigurator
      target={target}
      attribute={numberAttribute}
      validationErrors={validationErrors}
      onTargetAttributeChange={jest.fn()}
    />
  );

  expect(screen.getByText('error.key.decimal_separator')).toBeInTheDocument();
  expect(screen.queryByText('error.key.global')).not.toBeInTheDocument();
});
