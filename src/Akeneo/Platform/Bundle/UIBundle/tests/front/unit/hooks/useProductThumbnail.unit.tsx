import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {renderHook} from '@testing-library/react-hooks';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {useProductThumbnail} from '../../../../Resources/public/js/product/form/quantified-associations/hooks/useProductThumbnail';
import {ProductType} from '../../../../Resources/public/js/product/form/quantified-associations/models';

const productWithoutImage = {
  id: 1,
  identifier: 'bag',
  label: 'Nice bag',
  document_type: ProductType.Product,
  image: null,
  completeness: 100,
  variant_product_completenesses: null,
};

const productWithImage = {
  id: 2,
  identifier: 'image_bag',
  label: 'Nice image bag',
  document_type: ProductType.Product,
  image: {
    originalFileName: 'name.jpg',
    filePath: 'path/to/file/name.jpg',
  },
  completeness: 100,
  variant_product_completenesses: null,
};

const wrapper = ({children}) => <DependenciesProvider>{children}</DependenciesProvider>;

test('It returns the image path if the product has an image', async () => {
  const {result} = renderHook(() => useProductThumbnail(productWithImage), {wrapper});

  expect(result.current).toEqual('pim_enrich_media_show');
});

test('It returns the placeholder path if the product has no image', async () => {
  const {result} = renderHook(() => useProductThumbnail(productWithoutImage), {wrapper});

  expect(result.current).toEqual('/bundles/pimui/img/image_default.png');
});
