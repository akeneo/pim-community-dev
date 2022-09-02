import React from 'react';
import {screen} from '@testing-library/react';
import {EnabledConfigurator} from './EnabledConfigurator';
import {createAttributeDataMapping, createPropertyDataMapping} from '../../../../models';
import {ValidationError} from '@akeneo-pim-community/shared';
import {renderWithProviders} from 'feature/tests';

jest.mock('../../Operations');
jest.mock('../../Sources');

test('it displays a enabled configurator', async () => {
  const dataMapping = createPropertyDataMapping('enabled');

  await renderWithProviders(
    <EnabledConfigurator
      columns={[]}
      dataMapping={dataMapping}
      onOperationsChange={jest.fn()}
      onRefreshSampleData={jest.fn()}
      onTargetChange={jest.fn()}
      onSourcesChange={jest.fn()}
      validationErrors={[]}
    />
  );

  expect(screen.getByText('akeneo.tailored_import.data_mapping.target.title')).toBeInTheDocument();
  expect(screen.getByText('Sources')).toBeInTheDocument();
  expect(screen.getByText('Operations')).toBeInTheDocument();
});

test('it throws an error if we setup this component with a wrong target', async () => {
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

  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  await expect(async () => {
    await renderWithProviders(
      <EnabledConfigurator
        columns={[]}
        // @ts-expect-error invalid data mapping
        dataMapping={dataMapping}
        onOperationsChange={jest.fn()}
        onRefreshSampleData={jest.fn()}
        onSourcesChange={jest.fn()}
        onTargetChange={jest.fn()}
        validationErrors={[]}
      />
    );
  }).rejects.toThrow('Invalid target data "response_time" for enabled configurator');

  mockedConsole.mockRestore();
});

test('it should display validation errors', async () => {
  const dataMapping = createPropertyDataMapping('enabled');

  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.global',
      invalidValue: '',
      message: 'this is a global error',
      parameters: {},
      propertyPath: '',
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
    <EnabledConfigurator
      columns={[]}
      dataMapping={dataMapping}
      onRefreshSampleData={jest.fn()}
      onOperationsChange={jest.fn()}
      onTargetChange={jest.fn()}
      onSourcesChange={jest.fn()}
      validationErrors={validationErrors}
    />
  );

  expect(screen.getByText('error.key.sources')).toBeInTheDocument();
  expect(screen.queryByText('error.key.global')).not.toBeInTheDocument();
});
