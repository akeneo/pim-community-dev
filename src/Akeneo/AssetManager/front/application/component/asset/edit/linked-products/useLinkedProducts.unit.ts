import {act} from '@testing-library/react-hooks';
import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {useLinkedProducts} from './useLinkedProducts';

test('It fetches linked products', async () => {
  const response = {
    items: [
      {
        id: '12',
        identifier: 'alessi',
        document_type: 'product',
        label: 'Alessi',
        image: null,
        completeness: 100,
      },
    ],
    total_count: 1,
    matches_count: 1,
  };
  const expected = {
    items: [
      {
        id: '12',
        identifier: 'alessi',
        type: 'product',
        labels: {
          en_US: 'Alessi',
        },
        image: null,
        completeness: {
          ratio: 100,
          completeChildren: 0,
          totalChildren: 0,
        },
      },
    ],
    totalCount: 1,
    matchesCount: 1,
  };
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    status: 200,
    json: async () => response,
  }));

  const {result, waitForNextUpdate} = renderHookWithProviders(() =>
    useLinkedProducts('atmosphere', 'atmosphere-1', 'asset_collection', 'ecommerce', 'en_US')
  );

  await act(async () => {
    await waitForNextUpdate();
  });

  const [products, totalCount] = result.current;
  expect(products).toEqual(expected.items);
  expect(totalCount).toEqual(expected.totalCount);
});

test('It does not fetches linked products if attribute is not defined', async () => {
  global.fetch = jest.fn().mockImplementation();

  const {result} = renderHookWithProviders(() =>
    useLinkedProducts('atmosphere', 'atmosphere-1', null, 'ecommerce', 'en_US')
  );

  const [products, totalCount] = result.current;
  expect(products).toEqual(null);
  expect(totalCount).toEqual(0);
});
