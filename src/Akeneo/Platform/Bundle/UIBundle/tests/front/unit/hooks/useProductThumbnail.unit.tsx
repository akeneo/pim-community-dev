import {renderHookWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {useProductThumbnail} from '../../../../Resources/public/js/product/form/quantified-associations/hooks/useProductThumbnail';
import {ProductType} from '../../../../Resources/public/js/product/form/quantified-associations/models';

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

const productWithoutImage = {...productWithImage, image: null};
const productWithAssetManagerImage = {
  ...productWithImage,
  image: {
    originalFileName: 'name.jpg',
    filePath: '/rest/asset_manager/image_preview/nice-image.jpg',
  },
};

test('It returns the image path if the product has an image', () => {
  const {result} = renderHookWithProviders(() => useProductThumbnail(productWithImage));

  expect(result.current).toEqual('pim_enrich_media_show');
});

test('It returns the placeholder path if the product has no image', () => {
  const {result} = renderHookWithProviders(() => useProductThumbnail(productWithoutImage));

  expect(result.current).toEqual('/bundles/pimui/img/image_default.png');
});

test('It returns the ready-to-use filepath if the image is coming from the Asset Manager', () => {
  const {result} = renderHookWithProviders(() => useProductThumbnail(productWithAssetManagerImage));

  expect(result.current).toEqual('/rest/asset_manager/image_preview/nice-image.jpg');
});
