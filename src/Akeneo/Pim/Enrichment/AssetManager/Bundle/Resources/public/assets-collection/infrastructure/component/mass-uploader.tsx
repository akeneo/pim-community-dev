import * as React from 'react';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {useAssetFamily} from 'akeneoassetmanager/application/hooks/asset-family';
import {useChannels, ChannelFetcher} from 'akeneoassetmanager/application/hooks/channel';
import {Button} from 'akeneoassetmanager/application/component/app/button';
import {getLocales} from 'akeneoassetmanager/application/reducer/structure';
import UploadModal from 'akeneoassetmanager/application/asset-upload/component/modal';
import styled from 'styled-components';
import {Context} from 'akeneoassetmanager/domain/model/context';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {AssetFamilyFetcher} from 'akeneoassetmanager/domain/fetcher/asset-family';
import __ from 'akeneoassetmanager/tools/translator';

const UploadButton = styled(Button)`
  margin-left: 10px;
`;

type DataProvider = {
  assetFamilyFetcher: AssetFamilyFetcher;
  channelFetcher: ChannelFetcher;
};

export const MassUploader = React.memo(
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
    const [isOpen, setOpen] = React.useState(false);
    const assetFamily = useAssetFamily(dataProvider, assetFamilyIdentifier);
    const channels = useChannels(dataProvider.channelFetcher);
    const locales = getLocales(channels, context.channel);

    if (!assetFamily.rights.asset.upload) {
      return null;
    }

    return (
      <>
        <UploadButton
          title={__('pim_asset_manager.asset_collection.upload_asset')}
          buttonSize="medium"
          color="outline"
          isDisabled={!assetFamily.rights.asset.upload}
          onClick={() => setOpen(true)}
        >
          {__('pim_asset_manager.asset_collection.upload_asset')}
        </UploadButton>
        {isOpen && null !== assetFamily.assetFamily && (
          <UploadModal
            assetFamily={assetFamily.assetFamily}
            locale={context.locale}
            locales={locales}
            channels={channels}
            onCancel={() => setOpen(false)}
            onAssetCreated={(assetCodes: AssetCode[]) => {
              onAssetCreated(assetCodes);
              setOpen(false);
            }}
          />
        )}
      </>
    );
  }
);
