import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {renderHook} from '@testing-library/react-hooks';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {useProducts} from '../../../../Resources/public/js/product/form/quantified-associations/hooks/useProducts';

jest.mock('@akeneo-pim-community/legacy-bridge/provider/dependencies.ts');

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
