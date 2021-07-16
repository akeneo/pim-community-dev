import React from 'react';
import {Channel} from '@akeneo-pim-community/shared';
import {act} from 'react-dom/test-utils';
import {FetcherContext} from '../contexts';
import {useAssociationType, useAssociationTypes} from './useAssociationTypes';
import {renderHook} from '@testing-library/react-hooks';
import {AssociationType, Attribute} from '../models';

const response: AssociationType[] = [
  {code: 'X_SELL', labels: {}, is_quantified: false},
  {code: 'PACK', labels: {}, is_quantified: true},
];

const Wrapper: React.FC = ({children}) => {
  return (
    <FetcherContext.Provider
      value={{
        attribute: {fetchByIdentifiers: (): Promise<Attribute[]> => Promise.resolve<Attribute[]>([])},
        channel: {fetchAll: (): Promise<Channel[]> => new Promise(resolve => resolve([]))},
        associationType: {fetchByCodes: (): Promise<AssociationType[]> => new Promise(resolve => resolve(response))},
      }}
    >
      {children}
    </FetcherContext.Provider>
  );
};

test('It fetch the association types', async () => {
  const {result, waitForNextUpdate} = renderHook(() => useAssociationTypes(['X_SELL', 'PACK']), {wrapper: Wrapper});
  expect(result.current);
  await act(async () => {
    await waitForNextUpdate();
  });

  const associationTypes = result.current;
  expect(associationTypes).toEqual(response);
});

test('It fetch an association type', async () => {
  const {result, waitForNextUpdate} = renderHook(() => useAssociationType('X_SELL'), {wrapper: Wrapper});
  expect(result.current);
  await act(async () => {
    await waitForNextUpdate();
  });

  const associationType = result.current;
  expect(associationType).toEqual({code: 'X_SELL', labels: {}, is_quantified: false});
});

test('It returns null if no association type', async () => {
  const {result, waitForNextUpdate} = renderHook(() => useAssociationType('UPSELL'), {wrapper: Wrapper});
  await act(async () => {
    await waitForNextUpdate();
  });

  const attribute = result.current;
  expect(attribute).toBeNull();
});

test('It return association type only if hook is mounted', async () => {
  const {unmount} = renderHook(() => useAssociationTypes(['X_SELL']), {wrapper: Wrapper});
  unmount();
});

test('It returns attribute only if hook is mounted', async () => {
  const {unmount} = renderHook(() => useAssociationType('X_SELL'), {wrapper: Wrapper});

  unmount();
});
