import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {useCategoryChildrenFetcher} from './useCategoryChildrenFetcher';

test('It fetch the category children', async () => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => [
      {
        attr: {
          id: 'node_1',
          'data-code': 'mono_pompe',
        },
        data: 'Pompe toutes seules',
        state: 'leaf'
      },
      {
        attr: {
          id: 'node_2',
          'data-code': 'godasses',
        },
        data: 'Godasses',
        state: 'closed'
      }
    ],
  }));

  const {result} = renderHookWithProviders(() => useCategoryChildrenFetcher());
  const categoryChildrenFetcher = result.current;

  const response = await categoryChildrenFetcher(1);

  expect(response).toEqual([
    {
      id: 1,
      code: 'mono_pompe',
      label: 'Pompe toutes seules',
      isLeaf: true
    },
    {
      id: 2,
      code: 'godasses',
      label: 'Godasses',
      isLeaf: false
    },
  ]);
});
