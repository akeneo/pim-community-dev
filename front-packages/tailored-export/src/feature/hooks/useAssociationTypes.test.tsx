import React from "react";
import {Channel} from '@akeneo-pim-community/shared';
import {act} from 'react-dom/test-utils';
import {Attribute, FetcherContext} from '../contexts';
import {useAssociationTypes} from "./useAssociationTypes";
import {renderHook} from '@testing-library/react-hooks';
import {AssociationType} from "../models";

const response: AssociationType[] = [
  {code: 'X_SELL', labels: {}, is_quantified: false},
  {code: 'PACK', labels: {}, is_quantified: true}
];

const Wrapper: React.FC = ({children}) => {
  return (
    <FetcherContext.Provider value={{
      attribute: {fetchByIdentifiers: (): Promise<Attribute[]> => Promise.resolve<Attribute[]>([])},
      channel: {fetchAll: (): Promise<Channel[]> => new Promise(resolve => resolve([]))},
      associationType: {fetchByCodes: (): Promise<AssociationType[]> => new Promise(resolve => resolve(response))},
    }}>
      {children}
    </FetcherContext.Provider>
  )
};

test('It fetch the association types', async () => {
  const response: AssociationType[] = [
    {code: 'X_SELL', labels: {}, is_quantified: false},
    {code: 'PACK', labels: {}, is_quantified: true}
  ];

  const {result, waitForNextUpdate} = renderHook(() => useAssociationTypes(['X_SELL', 'PACK']), {wrapper: Wrapper});
  expect(result.current);
  await act(async () => {
    await waitForNextUpdate();
  });

  const associationTypes = result.current;
  expect(associationTypes).toEqual(response);
});

test('It return attributes only if hook is mounted', async () => {
  const {unmount} = renderHook(() => useAssociationTypes([]), {wrapper: Wrapper});
  unmount();
});
