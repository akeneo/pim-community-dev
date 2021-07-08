import React, {ReactNode} from 'react';
import {renderHook, act} from '@testing-library/react-hooks';
import {Channel} from '@akeneo-pim-community/shared';
import {FetcherContext} from '../contexts';
import {useAttribute, useAttributes} from './useAttributes';
import {Attribute} from '../models/Attribute';

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
  const response: Attribute[] = [
    {
      code: 'description',
      labels: {},
      scopable: true,
      localizable: true,
      type: 'pim_catalog_textarea',
      is_locale_specific: false,
      available_locales: [],
    },
    {
      code: 'name',
      labels: {},
      scopable: false,
      localizable: false,
      type: 'pim_catalog_text',
      is_locale_specific: false,
      available_locales: [],
    },
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
  const {result, waitForNextUpdate} = renderHook(() => useAttribute('description'), {
    wrapper: Wrapper,
    initialProps: {
      response: [
        {
          code: 'description',
          labels: {},
          scopable: true,
          localizable: true,
          type: 'pim_catalog_textarea',
          is_locale_specific: false,
          available_locales: [],
        },
      ],
    },
  });
  expect(result.current);
  await act(async () => {
    await waitForNextUpdate();
  });

  const attributeAfterFetch = result.current;
  expect(attributeAfterFetch).toEqual({
    code: 'description',
    labels: {},
    scopable: true,
    localizable: true,
    type: 'pim_catalog_textarea',
    is_locale_specific: false,
    available_locales: [],
  });
});

test('It returns null if no attribute', async () => {
  const {result} = renderHook(() => useAttribute('release_date'), {wrapper: Wrapper, initialProps: {response: []}});

  const attribute = result.current;
  expect(attribute).toBeNull();
});

test('It returns attributes only if hook is mounted', async () => {
  const {unmount} = renderHook(() => useAttributes(['release_date']), {
    wrapper: Wrapper,
    initialProps: {
      response: [
        {
          code: 'description',
          labels: {},
          scopable: true,
          localizable: true,
          type: 'pim_catalog_textarea',
          is_locale_specific: false,
          available_locales: [],
        },
      ],
    },
  });
  unmount();
});

test('It returns attribute only if hook is mounted', async () => {
  const {unmount} = renderHook(() => useAttribute('release_date'), {
    wrapper: Wrapper,
    initialProps: {
      response: [
        {
          code: 'description',
          labels: {},
          scopable: true,
          localizable: true,
          type: 'pim_catalog_textarea',
          is_locale_specific: false,
          available_locales: [],
        },
      ],
    },
  });
  unmount();
});
