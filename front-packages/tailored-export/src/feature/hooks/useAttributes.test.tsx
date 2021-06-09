import {Channel} from '@akeneo-pim-community/shared';
import {act} from 'react-dom/test-utils';
import {Attribute, FetcherContext} from '../contexts';
import {useAttribute, useAttributes} from './useAttributes';
import React, {ReactNode} from 'react';
import {renderHook} from '@testing-library/react-hooks';

type WrapperProps = {
  children?: ReactNode;
  response: Attribute[];
};

const Wrapper = ({children, response}: WrapperProps) => {
  return (
    <FetcherContext.Provider
      value={{
        attribute: {fetchByIdentifiers: (): Promise<Attribute[]> => Promise.resolve<Attribute[]>(response)},
        channel: {fetchAll: (): Promise<Channel[]> => new Promise(resolve => resolve([]))},
      }}
    >
      {children}
    </FetcherContext.Provider>
  );
};

test('It fetch the attributes', async () => {
  const response = [
    {code: 'description', labels: {}, scopable: true, localizable: true},
    {code: 'name', labels: {}, scopable: false, localizable: false},
  ];

  const {result, waitForNextUpdate} = renderHook(() => useAttributes(['name', 'description']), {
    wrapper: Wrapper,
    initialProps: {response},
  });
  expect(result.current);
  await act(async () => {
    await waitForNextUpdate();
  });

  const attributes = result.current;
  expect(attributes).toEqual(response);
});

test('It fetch an attribute', async () => {
  const {result, waitForNextUpdate} = renderHook(() => useAttribute('release_date'), {
    wrapper: Wrapper,
    initialProps: {response: [{code: 'description', labels: {}, scopable: true, localizable: true}]},
  });
  expect(result.current);
  await act(async () => {
    await waitForNextUpdate();
  });

  const attribute = result.current;
  expect(attribute).toEqual({code: 'description', labels: {}, scopable: true, localizable: true});
});

test('It return null if no attribute', async () => {
  const {result} = renderHook(() => useAttribute('release_date'), {wrapper: Wrapper, initialProps: {response: []}});

  const attribute = result.current;
  expect(attribute).toBeNull();
});

test('It return attributes only if hook is mounted', async () => {
  const {unmount} = renderHook(() => useAttributes([]), {
    wrapper: Wrapper,
    initialProps: {response: [{code: 'description', labels: {}, scopable: true, localizable: true}]},
  });
  unmount();
});

test('It return attribute only if hook is mounted', async () => {
  const {unmount} = renderHook(() => useAttribute('release_date'), {
    wrapper: Wrapper,
    initialProps: {response: [{code: 'description', labels: {}, scopable: true, localizable: true}]},
  });
  unmount();
});
