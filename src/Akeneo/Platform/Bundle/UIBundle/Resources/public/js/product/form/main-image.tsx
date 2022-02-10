import React from 'react';
import ReactDOM from 'react-dom';
import {getMediaPreviewUrl} from 'akeneoassetmanager/tools/media-url-generator';
import EditionAsset, {getEditionAssetMainMediaThumbnail} from 'akeneoassetmanager/domain/model/asset/edition-asset';
import {useAssetFetcher} from 'akeneoassetmanager/infrastructure/fetcher/useAssetFetcher';
import {ConfigProvider} from 'akeneoassetmanager/application/hooks/useConfig';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {getConfig} from 'pimui/js/config-registry';
const BasePefMainImage = require('pim/product-edit-form/main-image');
const FetcherRegistry = require('pim/fetcher-registry');
const Routing = require('routing');
const UserContext = require('pim/user-context');

const fallbackUrl = '/bundles/pimui/img/image_default.png';

type AssetImageProps = {
  assetFamilyIdentifier?: string;
  assetCode?: string;
};

// This main-image needs the customHook useAssetFetcher that can only be used inside a React component
const AssetImage = ({assetFamilyIdentifier, assetCode}: AssetImageProps) => {
  const assetFetcher = useAssetFetcher();
  const [asset, setAsset] = React.useState<EditionAsset | undefined>();

  React.useEffect(() => {
    if (!assetFamilyIdentifier || !assetCode) return;
    assetFetcher.fetch(assetFamilyIdentifier, assetCode).then(({asset}) => {
      setAsset(asset);
    });
  }, [assetFamilyIdentifier, assetCode]);

  const src = asset
    ? getMediaPreviewUrl(
        Routing,
        getEditionAssetMainMediaThumbnail(asset, UserContext.get('catalogScope'), UserContext.get('catalogLocale'))
      )
    : fallbackUrl;

  return <img className="AknTitleContainer-image" src={src} />;
};

class MainImage extends BasePefMainImage {
  render() {
    FetcherRegistry.getFetcher('family')
      .fetch(this.getRoot().getFormData().family)
      .then((family: any) => {
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
          this.el.src = Routing.generate('pim_enrich_media_show', {
            filename: encodeURIComponent(imageData.filePath),
            filter: 'thumbnail',
          });
          return;
        } else {
          // type = asset_collection
          if (imageData.length > 0) {
            FetcherRegistry.getFetcher('attribute')
              .fetch(attributeCode)
              .then((attribute: any) => {
                const assetFamilyIdentifier = attribute.reference_data_name;
                const assetCode = imageData[0];
                ReactDOM.render(
                  <ConfigProvider
                    config={{
                      value: getConfig('akeneoassetmanager/application/configuration/value') ?? {},
                      sidebar: getConfig('akeneoassetmanager/application/configuration/sidebar') ?? {},
                      attribute: getConfig('akeneoassetmanager/application/configuration/attribute') ?? {},
                    }}
                  >
                    <DependenciesProvider>
                      <AssetImage assetFamilyIdentifier={assetFamilyIdentifier} assetCode={assetCode} />
                    </DependenciesProvider>
                  </ConfigProvider>,
                  this.el.parentElement
                );
              });
          } else {
            this.el.src = fallbackUrl;
          }
        }
      });
  }

  remove(): any {
    this.unmount();
    return super.remove();
  }

  unmount(): any {
    ReactDOM.unmountComponentAtNode(this.el);
  }
}

export = MainImage;
