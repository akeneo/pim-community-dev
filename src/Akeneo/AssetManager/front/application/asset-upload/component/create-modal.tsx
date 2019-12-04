import * as React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import {Modal, Header, Title, ConfirmButton} from 'akeneoassetmanager/application/component/app/modal';
import {CloseButton} from 'akeneoassetmanager/application/component/app/close-button';
import AssetList from 'akeneoassetmanager/application/component/asset/create/asset-list';

type CreateModalProps = {
  onCancel: () => void;
  onAssetCreated: () => void;
};

const CreateModal = ({onCancel, onAssetCreated}: CreateModalProps) => {
  return (
    <Modal>
      <Header>
        <CloseButton title={__('pim_asset_manager.close')} onClick={onCancel} />
        <Title>{__('pim_asset_manager.asset.upload.title')}</Title>
        <ConfirmButton title={__('pim_asset_manager.asset.upload.confirm')} color="green" onClick={onAssetCreated}>
          {__('pim_asset_manager.asset.upload.confirm')}
        </ConfirmButton>
      </Header>
      <AssetList assets={[]} onAssetRemove={() => {}} />
    </Modal>
  );
};

export default CreateModal;
