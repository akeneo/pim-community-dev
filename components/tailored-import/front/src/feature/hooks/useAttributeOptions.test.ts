import {act} from '@testing-library/react-hooks';
import {useAttributeOptions} from './useAttributeOptions';
import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {AttributeOption} from '../models';

test('It fetches attributeOptions', async () => {
  const response: {
    items: AttributeOption[];
    matches_count: number;
  } = {
    items: [
      {
        code: 'red',
        labels: {
          en_US: 'Red',
        },
      },
    ],
    matches_count: 1,
  };
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => response,
  }));

  const {result, waitForNextUpdate} = renderHookWithProviders(() =>
    useAttributeOptions('color', 'my search', 1, [], [], true)
  );

  await act(async () => {
    await waitForNextUpdate();
  });

  const [attributeOptions, attributeOptionsTotalCount] = result.current;
  expect(attributeOptions).toEqual(response.items);
  expect(attributeOptionsTotalCount).toEqual(response.matches_count);
});

test('It returns attribute options only if hook is mounted', async () => {
  const response: {
    items: AttributeOption[];
    matches_count: number;
  } = {
    items: [
      {
        code: 'red',
        labels: {
          en_US: 'Red',
        },
      },
    ],
    matches_count: 1,
  };
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => response,
  }));
  const {result, unmount} = renderHookWithProviders(() => useAttributeOptions('color', 'my search', 1, [], [], true));
  unmount();

  const [attributeOptions, attributeOptionsTotalCount] = result.current;
  expect(attributeOptions).toEqual([]);
  expect(attributeOptionsTotalCount).toEqual(0);
});

test('It does not fetch when it should not', async () => {
  const response: {
    items: AttributeOption[];
    matches_count: number;
  } = {
    items: [
      {
        code: 'red',
        labels: {
          en_US: 'Red',
        },
      },
    ],
    matches_count: 1,
  };
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => response,
  }));
  const {result} = renderHookWithProviders(() => useAttributeOptions('color', 'my search', 1, [], [], false));

  const [attributeOptions, attributeOptionsTotalCount] = result.current;
  expect(attributeOptions).toEqual([]);
  expect(attributeOptionsTotalCount).toEqual(0);
  expect(global.fetch).not.toHaveBeenCalled();
});
