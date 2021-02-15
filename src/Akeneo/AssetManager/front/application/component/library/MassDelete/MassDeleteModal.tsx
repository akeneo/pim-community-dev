import React, {useState} from 'react';
import {Button, DeleteIllustration, Field, getColor, Key, Modal, TextInput, useShortcut} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import styled from 'styled-components';

type MassDeleteModalProps = {
  onConfirm: () => void;
  onCancel: () => void;
  selectedAssetCount: number;
  assetFamilyIdentifier: AssetFamilyIdentifier;
};

const Highlight = styled.span`
  color: ${getColor('brand', 100)};
  font-weight: bold;
`;

const SpacedField = styled(Field)`
  margin-top: 20px;
`;

const MassDeleteModal = ({onConfirm, onCancel, assetFamilyIdentifier, selectedAssetCount}: MassDeleteModalProps) => {
  const translate = useTranslate();
  const [assetFamilyConfirm, setAssetFamilyConfirm] = useState<string>('');
  const isValid = assetFamilyConfirm === assetFamilyIdentifier;

  const handleConfirm = async () => {
    if (!isValid) return;

    onConfirm();
  };

  useShortcut(Key.Enter, handleConfirm);

  return (
    <Modal closeTitle={translate('pim_common.close')} onClose={onCancel} illustration={<DeleteIllustration />}>
      <Modal.SectionTitle color="brand">{translate('pim_asset_manager.asset.mass_delete.title')}</Modal.SectionTitle>
      <Modal.Title>{translate('pim_common.confirm_deletion')}</Modal.Title>
      <Highlight>
        {translate('pim_asset_manager.asset.mass_delete.confirm', {assetCount: selectedAssetCount}, selectedAssetCount)}
      </Highlight>
      {translate('pim_asset_manager.asset.mass_delete.extra_information')}
      <SpacedField label={translate('pim_asset_manager.asset.mass_delete.confirm_label', {assetFamilyIdentifier})}>
        <TextInput value={assetFamilyConfirm} onChange={setAssetFamilyConfirm} />
      </SpacedField>
      <Modal.BottomButtons>
        <Button level="tertiary" onClick={onCancel}>
          {translate('pim_common.cancel')}
        </Button>
        <Button disabled={!isValid} level="danger" onClick={handleConfirm}>
          {translate('pim_common.delete')}
        </Button>
      </Modal.BottomButtons>
    </Modal>
  );
};

export {MassDeleteModal};
