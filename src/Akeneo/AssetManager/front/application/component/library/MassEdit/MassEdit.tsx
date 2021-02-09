import React, {useCallback} from 'react';
import {useTranslate, useNotify, NotificationLevel} from '@akeneo-pim-community/legacy-bridge';
import {Button, useBooleanState} from 'akeneo-design-system';
import {Query} from 'akeneoassetmanager/domain/fetcher/fetcher';
import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {MassEditModal} from 'akeneoassetmanager/application/component/library/MassEdit/MassEditModal';
import {Context} from 'akeneoassetmanager/domain/model/context';
import Channel from 'akeneoassetmanager/domain/model/channel';
import massEditLauncher from 'akeneoassetmanager/infrastructure/mass-edit-launcher';
import {Updater} from './model/updater';

type MassEditProps = {
  selectionQuery: Query | null;
  assetFamily: AssetFamily | null;
  context: Context;
  selectedCount: number;
  onConfirm: () => void;
  channels: Channel[];
}
const MassEdit = ({
  selectionQuery,
  assetFamily,
  context,
  onConfirm,
  selectedCount,
  channels,
}: MassEditProps) => {
  const translate = useTranslate();
  const [isMassEditModalOpen, openMassEditModal, closeMassEditModal] = useBooleanState(false);
  const notify = useNotify();

  const handleMassEdit = useCallback((updaterCollection: Updater[]) => {
    if (selectionQuery === null || assetFamily === null) return;

    try {
      massEditLauncher.launch(assetFamily.code, selectionQuery, updaterCollection);
      notify(NotificationLevel.SUCCESS, translate('pim_asset_manager.asset.notification.mass_edit.success'));
      closeMassEditModal();

      onConfirm();
    } catch (error) {
      notify(NotificationLevel.ERROR, translate('pim_asset_manager.asset.notification.mass_edit.fail', {error}));
    }
  }, [selectionQuery, closeMassEditModal, assetFamily]);

  return (
    <>
      <Button level="secondary" onClick={openMassEditModal}>
        {translate('pim_common.edit')}
      </Button>
      {isMassEditModalOpen && null !== assetFamily && (
        <MassEditModal
          assetFamily={assetFamily}
          selectedAssetCount={selectedCount}
          context={context}
          onConfirm={handleMassEdit}
          onCancel={closeMassEditModal}
          channels={channels}
        />
      )}
    </>
  );
};

export {MassEdit};
