import {act} from '@testing-library/react-hooks';
import {useAttribute, useAttributes} from './useAttributes';
import {Attribute} from '../models/Attribute';
import {renderHookWithProviders} from 'feature/tests';

const flushPromises = () => new Promise(setImmediate);

test('It fetches attributes', async () => {
  const response: Attribute[] = [
    {
      type: 'pim_catalog_text',
      code: 'name',
      labels: {fr_FR: 'French name', en_US: 'English name'},
      scopable: false,
      localizable: false,
      is_locale_specific: false,
      available_locales: [],
    },
    {
      type: 'pim_catalog_textarea',
      code: 'description',
      labels: {fr_FR: 'French description', en_US: 'English description'},
      scopable: true,
      localizable: true,
      is_locale_specific: false,
      available_locales: [],
    },
  ];

  const attributeCodes = ['name', 'description'];
  const {result} = renderHookWithProviders(() => useAttributes(attributeCodes));
  await act(async () => {
    await flushPromises();
  });

  const attributes = result.current;
  expect(attributes).toEqual(response);
});

test('It fetches an attribute', async () => {
  const {result} = renderHookWithProviders(() => useAttribute('description'));
  await act(async () => {
    await flushPromises();
  });

  const [isFetchingAfterFetch, attributeAfterFetch] = result.current;
  expect(attributeAfterFetch).toEqual({
    code: 'description',
    labels: {
      en_US: 'English description',
      fr_FR: 'French description',
    },
    localizable: true,
    scopable: true,
    type: 'pim_catalog_textarea',
    is_locale_specific: false,
    available_locales: [],
  });
  expect(isFetchingAfterFetch).toBe(false);
});

test('It returns null if no attribute', async () => {
  const {result} = renderHookWithProviders(() => useAttribute('release_date'));
  await act(async () => {
    await flushPromises();
  });

  const [isFetching, attribute] = result.current;
  expect(attribute).toBe(null);
  expect(isFetching).toBe(false);
});

test('It returns null during loading', async () => {
  const {result} = renderHookWithProviders(() => useAttribute('description'));
  const [isFetching, attribute] = result.current;
  expect(isFetching).toBe(true);
  expect(attribute).toBeNull();

  await act(async () => {
    await flushPromises();
  });

  const [isFetchingAfterFetch, attributeAfterFetch] = result.current;
  expect(isFetchingAfterFetch).toBe(false);
  expect(attributeAfterFetch).toEqual({
    code: 'description',
    labels: {
      en_US: 'English description',
      fr_FR: 'French description',
    },
    localizable: true,
    scopable: true,
    type: 'pim_catalog_textarea',
    is_locale_specific: false,
    available_locales: [],
  });
});

test('It returns null when switching attribute code', async () => {
  const {result, rerender} = renderHookWithProviders(({attributeCode}) => useAttribute(attributeCode), {
    attributeCode: 'description',
  });

  await act(async () => {
    await flushPromises();
    const [isFetching, attribute] = result.current;
    expect(isFetching).toBe(false);
    expect(attribute).toEqual({
      code: 'description',
      labels: {
        en_US: 'English description',
        fr_FR: 'French description',
      },
      localizable: true,
      scopable: true,
      type: 'pim_catalog_textarea',
      is_locale_specific: false,
      available_locales: [],
    });
  });

  await act(async () => {
    await rerender({attributeCode: 'release_date'});

    const [isFetching, attribute] = result.current;
    expect(attribute).toBeNull();
    expect(isFetching).toBe(false);
  });
});

test('It returns an empty array if attribute codes are empty', async () => {
  const {result, rerender} = renderHookWithProviders(({attributeCodes}) => useAttributes(attributeCodes), {
    attributeCodes: ['description'],
  });

  await act(async () => {
    await flushPromises();
    const attributes = result.current;
    expect(attributes).toEqual([
      {
        code: 'description',
        labels: {
          en_US: 'English description',
          fr_FR: 'French description',
        },
        localizable: true,
        scopable: true,
        type: 'pim_catalog_textarea',
        is_locale_specific: false,
        available_locales: [],
      },
    ]);
  });

  await act(async () => {
    await rerender({attributeCodes: []});

    const attribute = result.current;
    expect(attribute).toEqual([]);
  });
});

test('It returns attributes only if hook is mounted', async () => {
  const {unmount} = renderHookWithProviders(() => useAttributes(['release_date']));
  unmount();
});

test('It returns attribute only if hook is mounted', async () => {
  const {unmount} = renderHookWithProviders(() => useAttribute('release_date'));
  unmount();
});
