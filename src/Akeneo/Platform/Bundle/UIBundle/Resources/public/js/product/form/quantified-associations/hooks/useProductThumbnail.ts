import {useRoute} from '@akeneo-pim-community/legacy-bridge';
import {Product} from '../models';

const PLACEHOLDER_PATH = '/bundles/pimui/img/image_default.png';

const useProductThumbnail = (product: Product) =>
  null === product.image
    ? PLACEHOLDER_PATH
    : useRoute('pim_enrich_media_show', {
        filename: encodeURIComponent(product.image.filePath),
        filter: 'thumbnail',
      });

export {useProductThumbnail};
