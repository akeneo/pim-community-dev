import {useRoute} from '@akeneo-pim-community/legacy-bridge';
import {Product} from '../models';

const PLACEHOLDER_PATH = '/bundles/pimui/img/image_default.png';
const isAssetManagerImagePath = (path: string): boolean => path.includes('rest/asset_manager/image_preview');

const useProductThumbnail = (product: Product | null) => {
  const thumbnailUrl = useRoute(
    'pim_enrich_media_show',
    product?.image
      ? {
          filename: encodeURIComponent(product.image.filePath),
          filter: 'thumbnail',
        }
      : undefined
  );

  if (!product?.image) {
    return PLACEHOLDER_PATH;
  }

  if (isAssetManagerImagePath(product.image.filePath)) {
    return product.image.filePath;
  }

  return thumbnailUrl;
};

export {useProductThumbnail};
