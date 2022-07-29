import {act} from '@testing-library/react-hooks';
import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {Family} from '../models';
import {useFamilies} from './useFamilies';

test('It fetches Family', async () => {
  const response: {
    items: Family[];
    matches_count: number;
  } = {
    items: [
      {
        code: 'clothing',
        labels: {
          en_US: 'clothing',
        },
      },
    ],
    matches_count: 1,
  };
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => response,
  }));

  const {result, waitForNextUpdate} = renderHookWithProviders(() => useFamilies('clothing', 1, [], [], true));

  await act(async () => {
    await waitForNextUpdate();
  });

  const [families, matchesCount] = result.current;
  expect(families).toEqual(response.items);
  expect(matchesCount).toEqual(response.matches_count);
});

test('It returns family only if hook is mounted', async () => {
  const response: {
    items: Family[];
    matches_count: number;
  } = {
    items: [
      {
        code: 'clothing',
        labels: {
          en_US: 'clothing',
        },
      },
    ],
    matches_count: 1,
  };
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => response,
  }));
  const {result, unmount} = renderHookWithProviders(() => useFamilies('clothing', 1, [], [], true));
  unmount();

  const [families, matchesCount] = result.current;
  expect(families).toEqual([]);
  expect(matchesCount).toEqual(0);
});

test('It does not fetch when it should not', async () => {
  const response: {
    items: Family[];
    matches_count: number;
  } = {
    items: [
      {
        code: 'clothing',
        labels: {
          en_US: 'clothing',
        },
      },
    ],
    matches_count: 1,
  };
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => response,
  }));
  const {result} = renderHookWithProviders(() => useFamilies('clothing', 1, [], [], false));

  const [families, matchesCount] = result.current;
  expect(families).toEqual([]);
  expect(matchesCount).toEqual(0);
  expect(global.fetch).not.toHaveBeenCalled();
});
