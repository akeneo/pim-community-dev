import {act} from '@testing-library/react-hooks';
import {useRecords} from './useRecords';
import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {Record} from '../models';

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

  const {result, waitForNextUpdate} = renderHookWithProviders(() => useRecords('brand', 'my search', 1, [], [], true));

  await act(async () => {
    await waitForNextUpdate();
  });

  const [records, recordsTotalCount] = result.current;
  expect(records).toEqual(response.items);
  expect(recordsTotalCount).toEqual(response.matches_count);
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
  const {result, unmount} = renderHookWithProviders(() => useRecords('brand', 'my search', 1, [], [], true));
  unmount();

  const [records, recordsTotalCount] = result.current;
  expect(records).toEqual([]);
  expect(recordsTotalCount).toEqual(0);
});

test('It does not fetch when it should not', async () => {
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
  const {result} = renderHookWithProviders(() => useRecords('brand', 'my search', 1, [], [], false));

  const [records, recordsTotalCount] = result.current;
  expect(records).toEqual([]);
  expect(recordsTotalCount).toEqual(0);
  expect(global.fetch).not.toHaveBeenCalled();
});
