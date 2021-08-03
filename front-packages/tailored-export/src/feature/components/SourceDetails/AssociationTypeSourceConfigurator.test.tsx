import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {AssociationTypeSourceConfigurator} from './AssociationTypeSourceConfigurator';
import {AssociationTypeConfiguratorProps, Source} from '../../models';
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

jest.mock('./SimpleAssociationType/SimpleAssociationTypeConfigurator', () => ({
  SimpleAssociationTypeConfigurator: ({onSourceChange}: {onSourceChange: (updatedSource: Source) => void}) => (
    <button
      onClick={() =>
        onSourceChange({
          ...simpleAssociationTypeSource,
          selection: {type: 'code', entity_type: 'product_models', separator: ','},
        })
      }
    >
      Update simple association selection
    </button>
  ),
}));

jest.mock('./QuantifiedAssociationType/QuantifiedAssociationTypeConfigurator', () => ({
  QuantifiedAssociationTypeConfigurator: ({onSourceChange}: AssociationTypeConfiguratorProps) => (
    <button
      onClick={() =>
        onSourceChange({
          ...quantifiedAssociationTypeSource,
          selection: {type: 'code', entity_type: 'product_models', separator: ','},
        })
      }
    >
      Update quantified association selection
    </button>
  ),
}));

test('it displays a simple association type configurator', async () => {
  const onSourceChange = jest.fn();
  await renderWithProviders(
    <AssociationTypeSourceConfigurator
      source={simpleAssociationTypeSource}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(screen.getByText(/Update simple association selection/i));
  expect(onSourceChange).toHaveBeenCalledWith({
    ...simpleAssociationTypeSource,
    selection: {type: 'code', entity_type: 'product_models', separator: ','},
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

  userEvent.click(screen.getByText(/Update quantified association selection/i));
  expect(onSourceChange).toHaveBeenCalledWith({
    ...quantifiedAssociationTypeSource,
    selection: {type: 'code', entity_type: 'product_models', separator: ','},
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
