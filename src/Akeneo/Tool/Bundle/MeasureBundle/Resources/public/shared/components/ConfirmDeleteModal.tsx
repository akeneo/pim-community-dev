import React from 'react';
import styled from 'styled-components';
import {
  Modal,
  ModalCloseButton,
  ModalBodyWithIllustration,
  ModalTitle,
  ModalDescription,
} from 'akeneomeasure/shared/components/Modal';
import {TrashIllustration} from 'akeneomeasure/shared/illustrations/TrashIllustration';
import {useShortcut} from 'akeneomeasure/shared/hooks/use-shortcut';
import {Key} from 'akeneomeasure/shared/key';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {Button} from '@akeneo-pim-community/shared';

const StyledTitle = styled(ModalTitle)`
  margin-bottom: 0;
`;

const ButtonContainer = styled.div`
  margin-top: 30px;

  > :not(:first-child) {
    margin-left: 10px;
  }
`;

type ConfirmModalProps = {
  description: string;
  onConfirm: () => void;
  onCancel: () => void;
};

const ConfirmDeleteModal = ({description, onConfirm, onCancel}: ConfirmModalProps) => {
  const __ = useTranslate();

  useShortcut(Key.Escape, onCancel);

  return (
    <Modal>
      <ModalCloseButton title={__('pim_common.close')} onClick={onCancel} />
      <ModalBodyWithIllustration illustration={<TrashIllustration size={210} />}>
        <StyledTitle title={__('pim_common.confirm_deletion')} />
        <ModalDescription>{description}</ModalDescription>
        <ButtonContainer>
          <Button color="grey" onClick={onCancel}>
            {__('pim_common.cancel')}
          </Button>
          <Button color="red" onClick={onConfirm}>
            {__('pim_common.delete')}
          </Button>
        </ButtonContainer>
      </ModalBodyWithIllustration>
    </Modal>
  );
};

export {ConfirmDeleteModal};
