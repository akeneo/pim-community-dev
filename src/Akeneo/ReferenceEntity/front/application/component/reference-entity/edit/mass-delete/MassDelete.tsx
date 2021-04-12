import React, {useCallback} from 'react';
import {useTranslate, useNotify, NotificationLevel} from '@akeneo-pim-community/legacy-bridge';
import {Button, useBooleanState} from 'akeneo-design-system';
import {Query} from 'akeneoreferenceentity/domain/fetcher/fetcher';
import ReferenceEntity from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import recordRemover from 'akeneoreferenceentity/infrastructure/remover/record';
import {MassDeleteModal} from 'akeneoreferenceentity/application/component/reference-entity/edit/mass-delete/MassDeleteModal';

type MassDeleteProps = {
  selectionQuery: Query | null;
  referenceEntity: ReferenceEntity | null;
  selectedCount: number;
  onConfirm: () => void;
};

const MassDelete = ({selectionQuery, onConfirm, referenceEntity, selectedCount}: MassDeleteProps) => {
  const translate = useTranslate();
  const [isMassDeleteModalOpen, openMassDeleteModal, closeMassDeleteModal] = useBooleanState(false);
  const notify = useNotify();

  const handleMassDelete = useCallback(() => {
    if (selectionQuery === null || referenceEntity === null) return;

    try {
      recordRemover.removeFromQuery(referenceEntity.getIdentifier(), selectionQuery);
      notify(NotificationLevel.SUCCESS, translate('pim_reference_entity.record.notification.mass_delete.success'));
      closeMassDeleteModal();

      onConfirm();
    } catch (error) {
      notify(NotificationLevel.ERROR, translate('pim_reference_entity.record.notification.mass_delete.fail'));
    }
  }, [selectionQuery, closeMassDeleteModal, referenceEntity]);

  if (null === selectionQuery) return null;

  return (
    <>
      <Button level="danger" onClick={openMassDeleteModal}>
        {translate('pim_common.delete')}
      </Button>
      {isMassDeleteModalOpen && null !== referenceEntity && (
        <MassDeleteModal
          referenceEntityIdentifier={referenceEntity.getIdentifier()}
          selectedRecordCount={selectedCount}
          onConfirm={handleMassDelete}
          onCancel={closeMassDeleteModal}
        />
      )}
    </>
  );
};

export {MassDelete};
