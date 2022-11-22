import React, {FC, useState} from 'react';
import {Button, DeleteIllustration, Field, Modal, TextInput} from 'akeneo-design-system';
import {NotificationLevel, useNotify, useTranslate} from '@akeneo-pim-community/shared';
import {IdentifierGeneratorCode} from '../models';
import {useDeleteIdentifierGenerator, useValidateFormWithEnter} from '../hooks';

type DeleteGeneratorModalProps = {
  generatorCode: IdentifierGeneratorCode;
  onClose: () => void;
  onDelete: () => void;
};

const DeleteGeneratorModal: FC<DeleteGeneratorModalProps> = ({generatorCode, onClose, onDelete}) => {
  const translate = useTranslate();
  const notify = useNotify();
  const [isLoading] = useState<boolean>(false);
  const [attributeCodeConfirm, setAttributeCodeConfirm] = useState<string>('');

  const deleteIdentifierGenerator = useDeleteIdentifierGenerator();
  const isButtonDisabled = attributeCodeConfirm !== generatorCode;

  const confirmDelete = () => {
    if (!isButtonDisabled) {
      deleteIdentifierGenerator.mutate(generatorCode, {
        onSuccess: () => {
          notify(
            NotificationLevel.SUCCESS,
            translate('pim_identifier_generator.flash.delete.success', {code: generatorCode})
          );
          onDelete();
        },
        onError: () => {
          notify(NotificationLevel.ERROR, translate('pim_identifier_generator.flash.delete.error'));
        },
      });
    }
  };

  useValidateFormWithEnter(confirmDelete);

  return (
    <Modal closeTitle={translate('pim_common.close')} illustration={<DeleteIllustration />} onClose={onClose}>
      <Modal.SectionTitle color="brand">{translate('pim_identifier_generator.deletion.operations')}</Modal.SectionTitle>
      <Modal.Title>{translate('pim_common.confirm_deletion')}</Modal.Title>
      <div>{translate('pim_identifier_generator.deletion.confirmation')}</div>
      <Field label={translate('pim_identifier_generator.deletion.type', {code: generatorCode})}>
        <TextInput readOnly={isLoading} value={attributeCodeConfirm} onChange={setAttributeCodeConfirm} />
      </Field>
      <Modal.BottomButtons>
        <Button onClick={onClose} level="tertiary">
          {translate('pim_common.cancel')}
        </Button>
        <Button level="danger" disabled={isButtonDisabled} onClick={confirmDelete}>
          {translate('pim_common.delete')}
        </Button>
      </Modal.BottomButtons>
    </Modal>
  );
};

export {DeleteGeneratorModal};
