import React from 'react';
import {screen} from '@testing-library/react';
import {ValidationError} from '@akeneo-pim-community/shared';
import {createAttributeDataMapping} from '../../../../models';
import {renderWithProviders} from 'feature/tests';
import {IdentifierConfigurator} from './IdentifierConfigurator';

const getIdentifierAttribute = () => ({
  code: 'sku',
  type: 'pim_catalog_identifier',
  labels: {},
  scopable: false,
  localizable: false,
  is_locale_specific: false,
  available_locales: [],
});

jest.mock('../../Operations');
jest.mock('../../Sources');
jest.mock('../../AttributeTargetParameters');

test('It displays an identifier configurator', async () => {
  const attribute = getIdentifierAttribute();
  const dataMapping = createAttributeDataMapping('sku', attribute, []);

  await renderWithProviders(
    <IdentifierConfigurator
      columns={[]}
      dataMapping={dataMapping}
      onOperationsChange={jest.fn()}
      onRefreshSampleData={jest.fn()}
      onSourcesChange={jest.fn()}
      onTargetChange={jest.fn()}
      attribute={attribute}
      validationErrors={[]}
    />
  );

  expect(screen.getByText('akeneo.tailored_import.data_mapping.target.title')).toBeInTheDocument();
  expect(screen.getByText('akeneo.tailored_import.data_mapping.target.identifier')).toBeInTheDocument();
  expect(screen.queryByLabelText('akeneo.tailored_import.data_mapping.target.clear_if_empty')).not.toBeInTheDocument();
  expect(screen.getByText('Sources')).toBeInTheDocument();
  expect(screen.getByText('akeneo.tailored_import.data_mapping.operations.title')).toBeInTheDocument();
  expect(screen.getByText('akeneo.tailored_import.data_mapping.operations.identifier')).toBeInTheDocument();
});

test('it should display validation errors', async () => {
  const attribute = getIdentifierAttribute();
  const dataMapping = createAttributeDataMapping('sku', attribute, []);

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
    <IdentifierConfigurator
      columns={[]}
      dataMapping={dataMapping}
      onOperationsChange={jest.fn()}
      onRefreshSampleData={jest.fn()}
      onSourcesChange={jest.fn()}
      onTargetChange={jest.fn()}
      attribute={attribute}
      validationErrors={validationErrors}
    />
  );

  expect(screen.getByText('error.key.sources')).toBeInTheDocument();
  expect(screen.queryByText('error.key.global')).not.toBeInTheDocument();
});
