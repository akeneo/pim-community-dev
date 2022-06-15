import {act} from '@testing-library/react-hooks';
import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {CategoryTree} from '../models/Category';
import {useCategoryTrees} from './useCategoryTrees';

test('It fetches category trees', async () => {
  const response: CategoryTree[] = [
    {
      id: 1,
      code: 'shoes',
      labels: {
        en_US: 'Shoes',
      },
    },
    {
      id: 2,
      code: 'tshirt',
      labels: {
        en_US: 'T-Shirt',
      },
    },
    {
      id: 3,
      code: 'ceinturon',
      labels: {
        en_US: 'Ceinturone',
      },
    },
  ];

  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => response,
  }));

  const {result, waitForNextUpdate} = renderHookWithProviders(() => useCategoryTrees());

  await act(async () => {
    await waitForNextUpdate();
  });

  const categoryTrees = result.current;
  expect(categoryTrees).toEqual(response);
});
