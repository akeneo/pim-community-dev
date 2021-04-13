import React, {useState, useRef} from 'react';
import styled from 'styled-components';
import {Field, TextInput, useAutoFocus} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {DeleteModal} from '@akeneo-pim-community/shared';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';

type MassDeleteModalProps = {
  onConfirm: () => void;
  onCancel: () => void;
  selectedAssetCount: number;
  assetFamilyIdentifier: AssetFamilyIdentifier;
};

const SpacedField = styled(Field)`
  margin-top: 20px;
`;

const MassDeleteModal = ({onConfirm, onCancel, assetFamilyIdentifier, selectedAssetCount}: MassDeleteModalProps) => {
  const translate = useTranslate();
  const [assetFamilyConfirm, setAssetFamilyConfirm] = useState<string>('');
  const isValid = assetFamilyConfirm === assetFamilyIdentifier;
  const inputRef = useRef<HTMLInputElement>(null);

  useAutoFocus(inputRef);

  const handleConfirm = async () => {
    if (!isValid) return;

    onConfirm();
  };

  return (
    <DeleteModal
      title={translate('pim_asset_manager.asset.mass_delete.title')}
      onConfirm={handleConfirm}
      onCancel={onCancel}
      canConfirmDelete={isValid}
    >
      <p>
        {translate('pim_asset_manager.asset.mass_delete.confirm', {assetCount: selectedAssetCount}, selectedAssetCount)}
      </p>
      <p>{translate('pim_asset_manager.asset.mass_delete.extra_information')}</p>
      <SpacedField label={translate('pim_asset_manager.asset.mass_delete.confirm_label', {assetFamilyIdentifier})}>
        <TextInput
          ref={inputRef}
          value={assetFamilyConfirm}
          onChange={setAssetFamilyConfirm}
          onSubmit={handleConfirm}
        />
      </SpacedField>
    </DeleteModal>
  );
};

export {MassDeleteModal};
