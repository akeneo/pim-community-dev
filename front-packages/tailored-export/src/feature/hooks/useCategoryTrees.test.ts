import {act} from '@testing-library/react';
import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {useCategoryTrees} from './useCategoryTrees';

const categories = [
  {
    id: 0,
    code: 'webcam',
    parent: null,
    labels: {en_US: 'Webcam'},
  },
];

test('It fetches the categories', async () => {
  const setActiveCategoryTree = jest.fn(callback => callback());
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: () => Promise.resolve(categories),
  }));

  const {waitForNextUpdate, result} = renderHookWithProviders(() => useCategoryTrees([], setActiveCategoryTree));

  await act(async () => {
    await waitForNextUpdate();
  });

  expect(setActiveCategoryTree).toBeCalled();
  expect(global.fetch).toBeCalledWith('pim_importexport_category_tree_list', {
    headers: [
      ['Content-type', 'application/json'],
      ['X-Requested-With', 'XMLHttpRequest'],
    ],
    body: '[]',
    method: 'POST',
  });
  expect(result.current).toBe(categories);
});
