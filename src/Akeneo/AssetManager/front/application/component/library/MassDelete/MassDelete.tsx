import React, {useCallback} from 'react';
import {useTranslate, useNotify, NotificationLevel} from '@akeneo-pim-community/legacy-bridge';
import {Button, useBooleanState} from 'akeneo-design-system';
import {Query} from 'akeneoassetmanager/domain/fetcher/fetcher';
import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import assetRemover from 'akeneoassetmanager/infrastructure/remover/asset';
import {MassDeleteModal} from 'akeneoassetmanager/application/component/library/MassDelete/MassDeleteModal';

const MassDelete = ({
  selectionQuery,
  onConfirm,
  assetFamily,
  selectedCount,
}: {
  selectionQuery: Query | null;
  assetFamily: AssetFamily | null;
  selectedCount: number;
  onConfirm: () => void;
}) => {
  const translate = useTranslate();
  const [isMassDeleteModalOpen, openMassDeleteModal, closeMassDeleteModal] = useBooleanState(false);
  const notify = useNotify();

  const handleMassDelete = useCallback(() => {
    if (selectionQuery === null || assetFamily === null) return;

    try {
      assetRemover.removeFromQuery(assetFamily.code, selectionQuery);
      notify(NotificationLevel.SUCCESS, translate('pim_asset_manager.asset.notification.mass_delete.success'));
      closeMassDeleteModal();

      onConfirm();
    } catch (error) {
      notify(NotificationLevel.ERROR, translate('pim_asset_manager.asset.notification.mass_delete.fail'));
    }
  }, [selectionQuery, closeMassDeleteModal, assetFamily]);

  return (
    <>
      <Button level="danger" onClick={openMassDeleteModal}>
        {translate('pim_common.delete')}
      </Button>
      {isMassDeleteModalOpen && null !== assetFamily && (
        <MassDeleteModal
          assetFamilyIdentifier={assetFamily.code}
          selectedAssetCount={selectedCount}
          onConfirm={handleMassDelete}
          onCancel={closeMassDeleteModal}
        />
      )}
    </>
  );
};

export {MassDelete};
