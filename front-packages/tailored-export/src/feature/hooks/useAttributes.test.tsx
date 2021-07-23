import React, {ReactNode} from 'react';
import {renderHook, act} from '@testing-library/react-hooks';
import {Channel} from '@akeneo-pim-community/shared';
import {FetcherContext} from '../contexts';
import {useAttribute, useAttributes} from './useAttributes';
import {Attribute} from '../models/Attribute';
import {AssociationType} from '../models';

type WrapperProps = {
  children?: ReactNode;
  response: Attribute[];
};

const flushPromises = () => new Promise(setImmediate);

const Wrapper = ({children, response}: WrapperProps) => {
  return (
    <FetcherContext.Provider
      value={{
        attribute: {
          fetchByIdentifiers: (): Promise<Attribute[]> => {
            return new Promise(resolve => {
              act(() => resolve(response));
            });
          },
        },
        channel: {fetchAll: (): Promise<Channel[]> => new Promise(resolve => resolve([]))},
        associationType: {fetchByCodes: (): Promise<AssociationType[]> => Promise.resolve([])},
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

test('It returns false if no attribute', async () => {
  const {result, waitForNextUpdate} = renderHook(() => useAttribute('release_date'), {
    wrapper: Wrapper,
    initialProps: {response: []},
  });

  await act(async () => {
    await waitForNextUpdate();
  });
  const attribute = result.current;
  expect(attribute).toBe(false);
});

test('It returns null during loading', async () => {
  const {result} = renderHook(() => useAttribute('description'), {
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
  const attribute = result.current;
  expect(attribute).toBeNull();
  await act(async () => {
    await flushPromises();
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

test('It returns null when switching attribute code', async () => {
  const {result, rerender} = renderHook(({attributeCode}) => useAttribute(attributeCode), {
    wrapper: Wrapper,
    initialProps: {
      attributeCode: 'description',
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

  await act(async () => {
    await flushPromises();
    const attribute = result.current;
    expect(attribute).toEqual({
      code: 'description',
      labels: {},
      scopable: true,
      localizable: true,
      type: 'pim_catalog_textarea',
      is_locale_specific: false,
      available_locales: [],
    });
  });

  await act(async () => {
    await rerender({
      attributeCode: 'release_date',
      response: [
        {
          code: 'release_date',
          labels: {},
          scopable: true,
          localizable: true,
          type: 'pim_catalog_textarea',
          is_locale_specific: false,
          available_locales: [],
        },
      ],
    });

    const attribute = result.current;
    expect(attribute).toBeNull();
  });
});

test('It returns an empty array if attribute codes are empty', async () => {
  const {result, rerender} = renderHook(({attributeCodes}) => useAttributes(attributeCodes), {
    wrapper: Wrapper,
    initialProps: {
      attributeCodes: ['description'],
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

  await act(async () => {
    await flushPromises();
    const attributes = result.current;
    expect(attributes).toEqual([
      {
        code: 'description',
        labels: {},
        scopable: true,
        localizable: true,
        type: 'pim_catalog_textarea',
        is_locale_specific: false,
        available_locales: [],
      },
    ]);
  });

  await act(async () => {
    await rerender({
      attributeCodes: [],
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
    });

    const attribute = result.current;
    expect(attribute).toEqual([]);
  });
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
