import assetFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset';
import { getMediaPreviewUrl } from "akeneoassetmanager/tools/media-url-generator";
import { getEditionAssetMainMediaThumbnail } from "akeneoassetmanager/domain/model/asset/edition-asset";
import { AssetResult } from "akeneoassetmanager/infrastructure/fetcher/asset";
const BasePefMainImage = require('pim/product-edit-form/main-image');
const FetcherRegistry = require('pim/fetcher-registry');
const Routing = require('routing');
const UserContext = require('pim/user-context');

class MainImage extends BasePefMainImage {
  private assetsCache: {[assetCode: string]: AssetResult} = {};

  private fetchAssetWithCache = async (assetFamilyIdentifier: string, assetCode: string) => {
    if (!this.assetsCache[assetCode]) {
      const asset = await assetFetcher.fetch(assetFamilyIdentifier, assetCode);
      this.assetsCache[assetCode] = asset;
    }

    return this.assetsCache[assetCode];
  }

  render() {
    const fallbackUrl = '/bundles/pimui/img/image_default.png';

    FetcherRegistry.getFetcher('family').fetch(this.getRoot().getFormData().family).then((family: any) => {
      const attributeCode = family.attribute_as_image;
      if (!attributeCode) {
        this.el.src = fallbackUrl;
        return;
      }

      const imageValues = this.getRoot().getFormData().values[attributeCode];
      if (imageValues.length === 0) {
        this.el.src = fallbackUrl;
        return;
      }

      const imageValue = imageValues[0]; // As the attribute_as_image is not localizable, it can only have 1 value max.
      const imageData = imageValue.data;
      if (imageData.hasOwnProperty('filePath') && imageData.hasOwnProperty('originalFilename')) {
        // type = pim_catalog_image
        this.el.src = Routing.generate('pim_enrich_media_show', { 'filename': encodeURIComponent(imageData.filePath), 'filter': 'thumbnail' });
        return;
      } else {
        // type = asset_collection
        if (imageData.length > 0) {
          FetcherRegistry.getFetcher('attribute').fetch(attributeCode).then((attribute: any) => {
            const assetFamilyIdentifier = attribute.reference_data_name;
            const assetCode = imageData[0];
            this.fetchAssetWithCache(assetFamilyIdentifier, assetCode).then((result: AssetResult) => {
              this.el.src = getMediaPreviewUrl(getEditionAssetMainMediaThumbnail(
                result.asset,
                UserContext.get('catalogScope'),
                UserContext.get('catalogLocale')
              ));
            });
          });
        } else {
          this.el.src = fallbackUrl;
        }
      }
    });
  };
}

export = MainImage;
