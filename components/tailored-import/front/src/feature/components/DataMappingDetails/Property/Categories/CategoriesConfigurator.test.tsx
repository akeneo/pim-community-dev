import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {CategoriesConfigurator} from './CategoriesConfigurator';
import {createAttributeDataMapping, createPropertyDataMapping} from '../../../../models';
import {ValidationError} from '@akeneo-pim-community/shared';
import {renderWithProviders} from 'feature/tests';

jest.mock('../../Operations');
jest.mock('../../Sources');

test('it displays a categories configurator', async () => {
  const dataMapping = createPropertyDataMapping('categories');

  await renderWithProviders(
    <CategoriesConfigurator
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
  expect(screen.getByLabelText('akeneo.tailored_import.data_mapping.target.clear_if_empty')).toBeInTheDocument();
  expect(screen.getByText('Sources')).toBeInTheDocument();
  expect(screen.getByText('Operations')).toBeInTheDocument();
  expect(
    screen.getByLabelText('akeneo.tailored_import.data_mapping.target.action_if_not_empty.title')
  ).toBeInTheDocument();
});

test('it defines if the property should be cleared when empty', async () => {
  const dataMapping = createPropertyDataMapping('categories');
  const onTargetChange = jest.fn();

  await renderWithProviders(
    <CategoriesConfigurator
      columns={[]}
      dataMapping={dataMapping}
      onOperationsChange={jest.fn()}
      onRefreshSampleData={jest.fn()}
      onSourcesChange={jest.fn()}
      onTargetChange={onTargetChange}
      validationErrors={[]}
    />
  );

  userEvent.click(screen.getByLabelText('akeneo.tailored_import.data_mapping.target.clear_if_empty'));

  expect(onTargetChange).toHaveBeenCalledWith({
    ...dataMapping.target,
    action_if_empty: 'clear',
  });
});

test('it can change the assignation mode to use when the value is not empty', async () => {
  const dataMapping = createPropertyDataMapping('categories');
  const onTargetChange = jest.fn();

  await renderWithProviders(
    <CategoriesConfigurator
      columns={[]}
      dataMapping={dataMapping}
      onOperationsChange={jest.fn()}
      onRefreshSampleData={jest.fn()}
      onSourcesChange={jest.fn()}
      onTargetChange={onTargetChange}
      validationErrors={[]}
    />
  );

  userEvent.click(screen.getByTitle('pim_common.open'));
  userEvent.click(screen.getByText('akeneo.tailored_import.data_mapping.target.action_if_not_empty.add'));

  expect(onTargetChange).toHaveBeenCalledWith({
    ...dataMapping.target,
    action_if_not_empty: 'add',
  });
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
      <CategoriesConfigurator
        columns={[]}
        dataMapping={dataMapping}
        onOperationsChange={jest.fn()}
        onRefreshSampleData={jest.fn()}
        onSourcesChange={jest.fn()}
        onTargetChange={jest.fn()}
        validationErrors={[]}
      />
    );
  }).rejects.toThrow('Invalid target data "response_time" for categories configurator');

  mockedConsole.mockRestore();
});

test('it should display validation errors', async () => {
  const dataMapping = createPropertyDataMapping('categories');

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
    <CategoriesConfigurator
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
