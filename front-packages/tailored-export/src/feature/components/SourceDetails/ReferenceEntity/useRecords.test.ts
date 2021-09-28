import {act} from '@testing-library/react-hooks';
import {useRecords} from './useRecords';
import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {Record} from './Record';

test('It fetches records', async () => {
  const response: {
    items: Record[];
    matches_count: number;
  } = {
    items: [
      {
        code: 'alessi',
        labels: {
          en_US: 'Alessi',
        },
      },
    ],
    matches_count: 1,
  };
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => response,
  }));

  const {result, waitForNextUpdate} = renderHookWithProviders(() => useRecords('brand', 'my search', 1, [], []));

  await act(async () => {
    await waitForNextUpdate();
  });

  const [attributeOptions, attributeOptionsTotalCount] = result.current;
  expect(attributeOptions).toEqual(response.items);
  expect(attributeOptionsTotalCount).toEqual(response.matches_count);
});

test('It returns records only if hook is mounted', async () => {
  const response: {
    items: Record[];
    matches_count: number;
  } = {
    items: [
      {
        code: 'alessi',
        labels: {
          en_US: 'Alessi',
        },
      },
    ],
    matches_count: 1,
  };
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => response,
  }));
  const {result, unmount} = renderHookWithProviders(() => useRecords('brand', 'my search', 1, [], []));
  unmount();

  const [attributeOptions, attributeOptionsTotalCount] = result.current;
  expect(attributeOptions).toEqual([]);
  expect(attributeOptionsTotalCount).toEqual(0);
});
