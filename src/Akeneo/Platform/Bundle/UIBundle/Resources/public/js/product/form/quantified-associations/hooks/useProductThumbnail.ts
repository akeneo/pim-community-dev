import {useRoute} from '@akeneo-pim-community/legacy-bridge';
import {Product} from '../models';

const PLACEHOLDER_PATH = '/bundles/pimui/img/image_default.png';

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

  return product?.image ? thumbnailUrl : PLACEHOLDER_PATH;
};

export {useProductThumbnail};
