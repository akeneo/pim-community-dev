import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders, ValidationError} from '@akeneo-pim-community/shared';
import {Column} from 'feature/models';
import {Sources} from './Sources';

const columns: Column[] = [
  {
    uuid: '288d85cb-3ffb-432d-a422-d2c6810113ab',
    index: 0,
    label: 'Product identifier',
  },
  {
    uuid: 'dba0d9f8-2283-4a07-82b7-67e0435b7dcc',
    index: 1,
    label: 'Name',
  },
];

test('it displays columns as sources', () => {
  renderWithProviders(
    <Sources
      isMultiSource={false}
      sources={[columns[0].uuid]}
      columns={columns}
      validationErrors={[]}
      onSourcesChange={jest.fn()}
    />
  );

  expect(screen.getByText('Product identifier (A)')).toBeInTheDocument();
});

test('it can add a source using the add source dropdown', () => {
  const handleSourcesChange = jest.fn();

  renderWithProviders(
    <Sources
      isMultiSource={false}
      sources={[]}
      columns={columns}
      validationErrors={[]}
      onSourcesChange={handleSourcesChange}
    />
  );

  userEvent.click(screen.getByText('akeneo.tailored_import.data_mapping.sources.add.label'));
  userEvent.click(screen.getByText('Product identifier (A)'));

  expect(handleSourcesChange).toHaveBeenCalledWith([columns[0].uuid]);
});

test('it displays validation errors', () => {
  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.an_error',
      invalidValue: '',
      message: 'this is an error',
      parameters: {},
      propertyPath: '',
    },
  ];

  renderWithProviders(
    <Sources
      isMultiSource={false}
      sources={[columns[0].uuid]}
      columns={columns}
      validationErrors={validationErrors}
      onSourcesChange={jest.fn()}
    />
  );

  expect(screen.getByText('error.key.an_error')).toBeInTheDocument();
});

test('it cannot add source when limit is reached when single source', () => {
  renderWithProviders(
    <Sources
      isMultiSource={false}
      sources={['288d85cb-3ffb-432d-a422-d2c6810113ab']}
      columns={columns}
      validationErrors={[]}
      onSourcesChange={jest.fn()}
    />
  );

  expect(screen.queryByText('akeneo.tailored_import.data_mapping.sources.add.label')).not.toBeInTheDocument();
  expect(screen.getByText('akeneo.tailored_import.data_mapping.sources.add.helper')).toBeInTheDocument();
});

test('it cannot add source when limit is reached when multi source', () => {
  renderWithProviders(
    <Sources
      isMultiSource={true}
      sources={[
        '288d85cb-3ffb-432d-a422-d2c6810113ab',
        '288d85cb-3ffb-432d-a422-d2c6810113ac',
        '288d85cb-3ffb-432d-a422-d2c6810113ad',
        '288d85cb-3ffb-432d-a422-d2c6810113ae',
      ]}
      columns={columns}
      validationErrors={[]}
      onSourcesChange={jest.fn()}
    />
  );

  expect(screen.queryByText('akeneo.tailored_import.data_mapping.sources.add.label')).not.toBeInTheDocument();
  expect(screen.getByText('akeneo.tailored_import.data_mapping.sources.add.helper')).toBeInTheDocument();
});

test('it can add multiple sources if it handles it', () => {
  const handleChange = jest.fn();

  renderWithProviders(
    <Sources
      isMultiSource={true}
      sources={['288d85cb-3ffb-432d-a422-d2c6810113ab']}
      columns={columns}
      validationErrors={[]}
      onSourcesChange={handleChange}
    />
  );

  expect(screen.queryByText('akeneo.tailored_import.data_mapping.sources.add.helper')).not.toBeInTheDocument();

  userEvent.click(screen.getByText('akeneo.tailored_import.data_mapping.sources.add.label'));
  userEvent.click(screen.getByText('Name (B)'));

  expect(handleChange).toHaveBeenCalledWith([
    '288d85cb-3ffb-432d-a422-d2c6810113ab',
    'dba0d9f8-2283-4a07-82b7-67e0435b7dcc',
  ]);
});

test('it can remove a source', () => {
  const handleSourcesChange = jest.fn();

  renderWithProviders(
    <Sources
      isMultiSource={false}
      sources={['288d85cb-3ffb-432d-a422-d2c6810113ab']}
      columns={columns}
      validationErrors={[]}
      onSourcesChange={handleSourcesChange}
    />
  );

  userEvent.click(screen.getByTitle('pim_common.remove'));
  expect(handleSourcesChange).toHaveBeenCalledWith([]);
});
