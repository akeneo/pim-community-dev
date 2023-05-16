import {renderHookWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import fetchMock from 'jest-fetch-mock';
import {useCountEntities} from './useCountEntities';

test('it return the entities count', async () => {
  fetchMock.mockResponseOnce(
    JSON.stringify({
      count_categories: 168,
      count_category_trees: 4,
      count_channels: 3,
      count_locales: 3,
    }),
    {
      status: 200,
    }
  );

  const {result, waitForNextUpdate} = renderHookWithProviders(useCountEntities);
  await waitForNextUpdate();

  expect(result.current).toEqual({
    count_categories: 168,
    count_category_trees: 4,
    count_channels: 3,
    count_locales: 3,
  });
});
