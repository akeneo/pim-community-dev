import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from 'feature/tests';
import {ValidationError} from '@akeneo-pim-community/shared';
import {MultiReferenceEntityConfigurator} from './MultiReferenceEntityConfigurator';
import {createAttributeDataMapping, createPropertyDataMapping} from '../../../../models';

const getMultiReferenceEntityTarget = () => ({
  code: 'yellow',
  type: 'akeneo_reference_entity_collection',
  labels: {},
  scopable: false,
  localizable: false,
  is_locale_specific: false,
  available_locales: [],
});

jest.mock('../../Operations');
jest.mock('../../Sources');
jest.mock('../../AttributeTargetParameters');

test('it displays a multi reference entity configurator', async () => {
  const attribute = getMultiReferenceEntityTarget();
  const dataMapping = createAttributeDataMapping(attribute, []);

  await renderWithProviders(
    <MultiReferenceEntityConfigurator
      columns={[]}
      dataMapping={dataMapping}
      onOperationsChange={jest.fn()}
      onRefreshSampleData={jest.fn()}
      onTargetChange={jest.fn()}
      onSourcesChange={jest.fn()}
      attribute={attribute}
      validationErrors={[]}
    />
  );

  expect(screen.getByText('Attribute target parameters')).toBeInTheDocument();
  expect(screen.getByLabelText('akeneo.tailored_import.data_mapping.target.clear_if_empty')).toBeInTheDocument();
  expect(screen.getByText('Sources')).toBeInTheDocument();
  expect(screen.getByText('Operations')).toBeInTheDocument();
  expect(
    screen.getByLabelText('akeneo.tailored_import.data_mapping.target.action_if_not_empty.title')
  ).toBeInTheDocument();
});

test('it defines if the attribute should be cleared when empty', async () => {
  const attribute = getMultiReferenceEntityTarget();
  const dataMapping = createAttributeDataMapping(attribute, []);
  const onTargetChange = jest.fn();

  await renderWithProviders(
    <MultiReferenceEntityConfigurator
      columns={[]}
      dataMapping={dataMapping}
      onOperationsChange={jest.fn()}
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

test('it can change the assignation mode to use when the value is not empty', async () => {
  const attribute = getMultiReferenceEntityTarget();
  const dataMapping = createAttributeDataMapping(attribute, []);
  const onTargetChange = jest.fn();

  await renderWithProviders(
    <MultiReferenceEntityConfigurator
      columns={[]}
      dataMapping={dataMapping}
      onOperationsChange={jest.fn()}
      onRefreshSampleData={jest.fn()}
      onSourcesChange={jest.fn()}
      onTargetChange={onTargetChange}
      attribute={attribute}
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
  const attribute = getMultiReferenceEntityTarget();
  const dataMapping = createPropertyDataMapping('family');

  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  await expect(async () => {
    await renderWithProviders(
      <MultiReferenceEntityConfigurator
        columns={[]}
        // @ts-expect-error invalid target
        dataMapping={dataMapping}
        onOperationsChange={jest.fn()}
        onRefreshSampleData={jest.fn()}
        onSourcesChange={jest.fn()}
        onTargetChange={jest.fn()}
        attribute={attribute}
        validationErrors={[]}
      />
    );
  }).rejects.toThrow('Invalid target data "family" for multi reference entity configurator');

  mockedConsole.mockRestore();
});

test('it should display validation errors', async () => {
  const attribute = getMultiReferenceEntityTarget();
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
    <MultiReferenceEntityConfigurator
      columns={[]}
      dataMapping={dataMapping}
      onRefreshSampleData={jest.fn()}
      onOperationsChange={jest.fn()}
      onTargetChange={jest.fn()}
      onSourcesChange={jest.fn()}
      attribute={attribute}
      validationErrors={validationErrors}
    />
  );

  expect(screen.getByText('error.key.sources')).toBeInTheDocument();
  expect(screen.getByText('error.key.target')).toBeInTheDocument();
  expect(screen.queryByText('error.key.global')).not.toBeInTheDocument();
});
