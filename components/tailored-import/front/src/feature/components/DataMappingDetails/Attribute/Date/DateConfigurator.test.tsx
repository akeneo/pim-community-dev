import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {ValidationError} from '@akeneo-pim-community/shared';
import {renderWithProviders} from 'feature/tests';
import {createAttributeDataMapping, createPropertyDataMapping} from '../../../../models';
import {DateConfigurator} from './DateConfigurator';

jest.mock('../../Operations');
jest.mock('../../Sources');
jest.mock('../../AttributeTargetParameters');

const getDateAttribute = () => ({
  code: 'birthday',
  type: 'pim_catalog_date',
  labels: {},
  scopable: false,
  localizable: false,
  is_locale_specific: false,
  available_locales: [],
});

test('it displays all date formats when opening the dropdown', async () => {
  const dateAttribute = getDateAttribute();
  const dataMapping = createAttributeDataMapping(dateAttribute, []);

  await renderWithProviders(
    <DateConfigurator
      columns={[]}
      dataMapping={dataMapping}
      onRefreshSampleData={jest.fn()}
      onOperationsChange={jest.fn()}
      onTargetChange={jest.fn()}
      onSourcesChange={jest.fn()}
      attribute={dateAttribute}
      validationErrors={[]}
    />
  );

  userEvent.click(screen.getByTitle('pim_common.open'));

  expect(screen.getAllByText('yyyy-mm-dd')).toHaveLength(2);
  expect(screen.getByText('dd/mm/yyyy')).toBeInTheDocument();
  expect(screen.getByText('yyyy.mm.dd')).toBeInTheDocument();
});

test('it defines the date format of the target', async () => {
  const dateAttribute = getDateAttribute();
  const dataMapping = createAttributeDataMapping(dateAttribute, []);
  const onTargetChange = jest.fn();

  await renderWithProviders(
    <DateConfigurator
      columns={[]}
      dataMapping={dataMapping}
      onRefreshSampleData={jest.fn()}
      onOperationsChange={jest.fn()}
      onTargetChange={onTargetChange}
      onSourcesChange={jest.fn()}
      attribute={dateAttribute}
      validationErrors={[]}
    />
  );

  userEvent.click(screen.getByTitle('pim_common.open'));
  userEvent.click(screen.getByText('yyyy/mm/dd'));

  expect(onTargetChange).toHaveBeenCalledWith({
    ...dataMapping.target,
    source_configuration: {
      date_format: 'yyyy/mm/dd',
    },
  });
});

test('it defines if the attribute should be cleared when empty', async () => {
  const attribute = getDateAttribute();
  const dataMapping = createAttributeDataMapping(attribute, []);
  const onTargetChange = jest.fn();

  await renderWithProviders(
    <DateConfigurator
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
  const dateAttribute = getDateAttribute();
  const dataMapping = createPropertyDataMapping('family');

  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  await expect(async () => {
    await renderWithProviders(
      <DateConfigurator
        columns={[]}
        // @ts-expect-error invalid data mapping type
        dataMapping={dataMapping}
        onRefreshSampleData={jest.fn()}
        onTargetChange={jest.fn()}
        onSourcesChange={jest.fn()}
        attribute={dateAttribute}
        validationErrors={[]}
      />
    );
  }).rejects.toThrow('Invalid target data "family" for date configurator');

  mockedConsole.mockRestore();
});

test('it should display validation errors', async () => {
  const attribute = getDateAttribute();
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
      messageTemplate: 'error.key.date_format',
      invalidValue: '#',
      message: 'this is a date format error',
      parameters: {},
      propertyPath: '[target][source_configuration][date_format]',
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
      message: 'this is a sources error',
      parameters: {},
      propertyPath: '[sources]',
    },
  ];

  await renderWithProviders(
    <DateConfigurator
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
  expect(screen.getByText('error.key.date_format')).toBeInTheDocument();
  expect(screen.queryByText('error.key.global')).not.toBeInTheDocument();
});
