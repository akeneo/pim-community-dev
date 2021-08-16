import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {AssociationTypeSourceConfigurator} from './AssociationTypeSourceConfigurator';
import {Source} from '../../models';
import {renderWithProviders} from 'feature/tests';

const simpleAssociationTypeSource: Source = {
  uuid: 'cffd560e-1e40-4c55-a415-89c7958b270d',
  code: 'XSELL',
  type: 'association_type',
  locale: null,
  channel: null,
  operations: [],
  selection: {
    type: 'code',
    entity_type: 'products',
    separator: ',',
  },
};

const quantifiedAssociationTypeSource: Source = {
  uuid: 'cffd560e-1e40-4c55-a415-89c7958b270d',
  code: 'PACK',
  type: 'association_type',
  locale: null,
  channel: null,
  operations: [],
  selection: {
    type: 'code',
    entity_type: 'products',
    separator: ',',
  },
};

test('it displays a simple association type configurator', async () => {
  const onSourceChange = jest.fn();

  await renderWithProviders(
    <AssociationTypeSourceConfigurator
      source={simpleAssociationTypeSource}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(
    screen.getByLabelText('akeneo.tailored_export.column_details.sources.selection.collection_separator.title')
  );
  userEvent.click(
    screen.getByText('akeneo.tailored_export.column_details.sources.selection.collection_separator.pipe')
  );

  expect(onSourceChange).toHaveBeenCalledWith({
    ...simpleAssociationTypeSource,
    selection: {type: 'code', entity_type: 'products', separator: '|'},
  });
});

test('it displays a quantified association type configurator', async () => {
  const onSourceChange = jest.fn();

  await renderWithProviders(
    <AssociationTypeSourceConfigurator
      source={quantifiedAssociationTypeSource}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(screen.getByLabelText('pim_common.type'));
  userEvent.click(
    screen.getByText('akeneo.tailored_export.column_details.sources.selection.quantified_association.quantity')
  );

  expect(onSourceChange).toHaveBeenCalledWith({
    ...quantifiedAssociationTypeSource,
    selection: {type: 'quantity', entity_type: 'products', separator: ','},
  });
});

test('it displays association type errors when attribute does not exist', async () => {
  const handleSourceChange = jest.fn();
  const source: Source = {
    uuid: 'cffd560e-1e40-4c55-a415-89c7958b270d',
    code: 'invalid_association_type',
    type: 'association_type',
    locale: null,
    channel: null,
    operations: [],
    selection: {
      type: 'code',
      entity_type: 'products',
      separator: ',',
    },
  };

  await renderWithProviders(
    <AssociationTypeSourceConfigurator
      source={source}
      validationErrors={[
        {
          messageTemplate: 'code error message',
          parameters: {},
          message: '',
          propertyPath: '',
          invalidValue: '',
        },
      ]}
      onSourceChange={handleSourceChange}
    />
  );

  expect(screen.getByText('code error message')).toBeInTheDocument();
});

test('it displays association type errors when attribute is found', async () => {
  const handleSourceChange = jest.fn();
  const source: Source = {
    uuid: 'cffd560e-1e40-4c55-a415-89c7958b270d',
    code: 'XSELL',
    type: 'association_type',
    locale: null,
    channel: null,
    operations: [],
    selection: {
      type: 'code',
      entity_type: 'products',
      separator: ',',
    },
  };

  await renderWithProviders(
    <AssociationTypeSourceConfigurator
      source={source}
      validationErrors={[
        {
          messageTemplate: 'code error message',
          parameters: {},
          message: '',
          propertyPath: '',
          invalidValue: '',
        },
      ]}
      onSourceChange={handleSourceChange}
    />
  );

  expect(screen.getByText('code error message')).toBeInTheDocument();
});

test('it renders an invalid association type placeholder when the source is invalid', async () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const handleSourceChange = jest.fn();

  await renderWithProviders(
    <AssociationTypeSourceConfigurator
      source={{
        uuid: 'cffd560e-1e40-4c55-a415-89c7958b270d',
        code: 'XSELL',
        type: 'association_type',
        locale: null,
        channel: null,
        operations: [],
        // @ts-expect-error invalid selection
        selection: {},
      }}
      validationErrors={[]}
      onSourceChange={handleSourceChange}
    />
  );

  expect(
    screen.getByText('akeneo.tailored_export.column_details.sources.invalid_source.association_type')
  ).toBeInTheDocument();
  mockedConsole.mockRestore();
});
