import React from 'react';
import {Button, useBooleanState} from 'akeneo-design-system';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {useAssetFamily} from 'akeneoassetmanager/application/hooks/asset-family';
import {useChannels, ChannelFetcher} from 'akeneoassetmanager/application/hooks/channel';
import {getLocales} from 'akeneoassetmanager/application/reducer/structure';
import {UploadModal} from 'akeneoassetmanager/application/asset-upload/component/modal';
import {Context} from 'akeneoassetmanager/domain/model/context';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {AssetFamilyFetcher} from 'akeneoassetmanager/domain/fetcher/asset-family';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

type DataProvider = {
  assetFamilyFetcher: AssetFamilyFetcher;
  channelFetcher: ChannelFetcher;
};

const MassUploader = React.memo(
  ({
    assetFamilyIdentifier,
    context,
    onAssetCreated,
    dataProvider,
  }: {
    assetFamilyIdentifier: AssetFamilyIdentifier;
    context: Context;
    onAssetCreated: (assetCodes: AssetCode[]) => void;
    dataProvider: DataProvider;
  }) => {
    const [isOpen, open, close] = useBooleanState();
    const {assetFamily, rights} = useAssetFamily(dataProvider, assetFamilyIdentifier);
    const channels = useChannels(dataProvider.channelFetcher);
    const locales = getLocales(channels, context.channel);
    const translate = useTranslate();

    if (!rights.asset.upload) {
      return null;
    }

    return (
      <>
        <Button
          title={translate('pim_asset_manager.asset_collection.upload_asset')}
          size="small"
          level="tertiary"
          ghost={true}
          disabled={!rights.asset.upload}
          onClick={open}
        >
          {translate('pim_asset_manager.asset_collection.upload_asset')}
        </Button>
        {isOpen && null !== assetFamily && (
          <UploadModal
            confirmLabel={translate('pim_asset_manager.asset.upload.add_to_product')}
            assetFamily={assetFamily}
            locale={context.locale}
            locales={locales}
            channels={channels}
            onCancel={close}
            onAssetCreated={
              /* istanbul ignore next */
              (assetCodes: AssetCode[]) => {
                onAssetCreated(assetCodes);
                close();
              }
            }
          />
        )}
      </>
    );
  }
);

export {MassUploader};
