import React, {ReactNode} from 'react';
import {screen, act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders as baseRender, Channel} from '@akeneo-pim-community/shared';
import {AssociationTypeSourceConfigurator} from './AssociationTypeSourceConfigurator';
import {AssociationType, Attribute, Source} from '../../models';
import {FetcherContext} from '../../contexts';

const simpleAssociationType: AssociationType = {
  code: 'XSELL',
  labels: {},
  is_quantified: false,
};

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

const fetchers = {
  attribute: {fetchByIdentifiers: (): Promise<Attribute[]> => Promise.resolve<Attribute[]>([])},
  channel: {fetchAll: (): Promise<Channel[]> => Promise.resolve<Channel[]>([])},
  associationType: {fetchByCodes: (): Promise<AssociationType[]> => Promise.resolve([simpleAssociationType])},
};

const renderWithProviders = async (node: ReactNode) =>
  await act(async () => void baseRender(<FetcherContext.Provider value={fetchers}>{node}</FetcherContext.Provider>));

jest.mock('./SimpleAssociationType/SimpleAssociationTypeConfigurator', () => ({
  SimpleAssociationTypeConfigurator: ({onSourceChange}: {onSourceChange: (updatedSource: Source) => void}) => (
    <button
      onClick={() =>
        onSourceChange({...source, selection: {type: 'code', entity_type: 'product_models', separator: ','}})
      }
    >
      Update selection
    </button>
  ),
}));

test('it displays association type configurator', async () => {
  const onSourceChange = jest.fn();
  await renderWithProviders(
    <AssociationTypeSourceConfigurator source={source} validationErrors={[]} onSourceChange={onSourceChange} />
  );

  userEvent.click(screen.getByText(/Update selection/i));
  expect(onSourceChange).toHaveBeenCalledWith({
    ...source,
    selection: {type: 'code', entity_type: 'product_models', separator: ','},
  });
});
