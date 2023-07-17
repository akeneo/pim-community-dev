import React, {ReactNode, useRef, ReactElement} from 'react';
import {Button, DeleteIllustration, Modal, useAutoFocus} from 'akeneo-design-system';
import {useTranslate} from '../hooks';
import {IllustrationProps} from 'akeneo-design-system/lib/illustrations/IllustrationProps';

type DeleteModalProps = {
  title: string;
  children: ReactNode;
  confirmDeletionTitle?: string;
  confirmButtonLabel?: string;
  cancelButtonLabel?: string;
  canConfirmDelete?: boolean;
  onConfirm: () => void;
  onCancel: () => void;
  illustration?: ReactElement<IllustrationProps>;
};

const DeleteModal = ({
  children,
  title,
  confirmDeletionTitle,
  confirmButtonLabel,
  cancelButtonLabel,
  canConfirmDelete = true,
  onConfirm,
  onCancel,
  illustration,
}: DeleteModalProps) => {
  const translate = useTranslate();
  const cancelRef = useRef(null);
  useAutoFocus(cancelRef);

  return (
    <Modal closeTitle={translate('pim_common.close')} onClose={onCancel} illustration={illustration ?? <DeleteIllustration />}>
      <Modal.SectionTitle color="brand">{title}</Modal.SectionTitle>
      <Modal.Title>{confirmDeletionTitle ?? translate('pim_common.confirm_deletion')}</Modal.Title>
      {children}
      <Modal.BottomButtons>
        <Button level="tertiary" onClick={onCancel} ref={cancelRef}>
          {cancelButtonLabel ?? translate('pim_common.cancel')}
        </Button>
        <Button level="danger" disabled={!canConfirmDelete} onClick={onConfirm}>
          {confirmButtonLabel ?? translate('pim_common.delete')}
        </Button>
      </Modal.BottomButtons>
    </Modal>
  );
};

export type {DeleteModalProps};
export {DeleteModal};
