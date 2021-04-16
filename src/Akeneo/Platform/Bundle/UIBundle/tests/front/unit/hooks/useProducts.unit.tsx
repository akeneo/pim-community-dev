import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {renderHook, act} from '@testing-library/react-hooks';
import {DependenciesProvider} from '@akeneo-pim-community/shared';
import {useProducts} from '../../../../Resources/public/js/product/form/quantified-associations/hooks/useProducts';

declare global {
  namespace NodeJS {
    interface Global {
      fetch: any;
    }
  }
}

const wrapper = ({children}) => <DependenciesProvider>{children}</DependenciesProvider>;

afterEach(() => {
  global.fetch && global.fetch.mockClear();
  delete global.fetch;
});

test('It returns the fetched product list', async () => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    json: () =>
      Promise.resolve({
        items: [
          {
            id: 1,
            identifier: 'bag',
            label: 'Nice bag',
            document_type: 'product',
            image: null,
            completeness: 100,
            variant_product_completenesses: null,
          },
        ],
      }),
  }));

  const {result, waitForNextUpdate} = renderHook(() => useProducts({products: ['bag'], product_models: []}), {wrapper});

  await waitForNextUpdate();

  expect(result.current).toEqual([
    {
      id: 1,
      identifier: 'bag',
      label: 'Nice bag',
      document_type: 'product',
      image: null,
      completeness: 100,
      variant_product_completenesses: null,
    },
  ]);
});

test('It does not fetch products if already fetched', async () => {
  global.fetch = jest.fn().mockImplementation(async (url, options) => ({
    json: () => {
      const body = JSON.parse(options.body);
      expect(body.products.length).toBeLessThanOrEqual(1);
      expect(body.product_models.length).toBeLessThanOrEqual(1);

      if (body.products[0] === 'bag') {
        return Promise.resolve({
          items: [
            {
              id: 1,
              identifier: 'bag',
              label: 'Nice bag',
              document_type: 'product',
              image: null,
              completeness: 100,
              variant_product_completenesses: null,
            },
          ],
        });
      } else {
        return Promise.resolve({
          items: [
            {
              id: 2,
              identifier: 'another_bag',
              label: 'Another hat',
              document_type: 'product',
              image: null,
              completeness: 100,
              variant_product_completenesses: null,
            },
            {
              id: 3,
              identifier: 'braided-hat',
              label: 'Braided hat',
              document_type: 'product_model',
              image: null,
              completeness: null,
              variant_product_completenesses: {
                completeChildren: 0,
                totalChildren: 1,
              },
            },
          ],
        });
      }
    },
  }));

  const identifiers = {products: ['bag'], product_models: []};

  const {result, waitForNextUpdate, rerender} = renderHook(() => useProducts(identifiers), {
    wrapper,
  });

  await waitForNextUpdate();

  expect(result.current).toEqual([
    {
      id: 1,
      identifier: 'bag',
      label: 'Nice bag',
      document_type: 'product',
      image: null,
      completeness: 100,
      variant_product_completenesses: null,
    },
  ]);

  identifiers.products = ['bag', 'another_bag'];
  identifiers.product_models = ['braided-hat'];

  act(() => {
    rerender();
  });

  await waitForNextUpdate();

  expect(result.current).toEqual([
    {
      id: 2,
      identifier: 'another_bag',
      label: 'Another hat',
      document_type: 'product',
      image: null,
      completeness: 100,
      variant_product_completenesses: null,
    },
    {
      id: 3,
      identifier: 'braided-hat',
      label: 'Braided hat',
      document_type: 'product_model',
      image: null,
      completeness: null,
      variant_product_completenesses: {
        completeChildren: 0,
        totalChildren: 1,
      },
    },
    {
      id: 1,
      identifier: 'bag',
      label: 'Nice bag',
      document_type: 'product',
      image: null,
      completeness: 100,
      variant_product_completenesses: null,
    },
  ]);
});
