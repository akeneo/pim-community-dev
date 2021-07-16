import React, {ReactNode} from 'react';
import {screen, act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders as baseRender, Channel} from '@akeneo-pim-community/shared';
import {AssociationTypeSourceConfigurator} from './AssociationTypeSourceConfigurator';
import {AssociationType, AssociationTypeConfiguratorProps, Attribute, Source} from '../../models';
import {FetcherContext} from '../../contexts';

const associationTypes: AssociationType[] = [
  {
    code: 'XSELL',
    labels: {},
    is_quantified: false,
  },
  {
    code: 'PACK',
    labels: {},
    is_quantified: true,
  },
];

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

const fetchers = {
  attribute: {fetchByIdentifiers: (): Promise<Attribute[]> => Promise.resolve<Attribute[]>([])},
  channel: {fetchAll: (): Promise<Channel[]> => Promise.resolve<Channel[]>([])},
  associationType: {
    fetchByCodes: (associationTypeCodes: string[]): Promise<AssociationType[]> =>
      Promise.resolve(associationTypes.filter(({code}) => associationTypeCodes.includes(code))),
  },
};

const renderWithProviders = async (node: ReactNode) =>
  await act(async () => void baseRender(<FetcherContext.Provider value={fetchers}>{node}</FetcherContext.Provider>));

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
