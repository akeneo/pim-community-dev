import React, {FC, useState} from 'react';
import {Button, DeleteIllustration, Field, Modal, TextInput} from 'akeneo-design-system';
import {NotificationLevel, useNotify, useRouter, useTranslate} from '@akeneo-pim-community/shared';
import {IdentifierGeneratorCode} from '../models';
import {useGetGenerators} from '../hooks';

type DeleteGeneratorModalProps = {
  generatorCode: IdentifierGeneratorCode;
  onClose: () => void;
  onDelete: () => void;
};

const DeleteGeneratorModal: FC<DeleteGeneratorModalProps> = ({generatorCode, onClose, onDelete}) => {
  const translate = useTranslate();
  const router = useRouter();
  const notify = useNotify();
  const {refetch} = useGetGenerators();
  const [isLoading] = useState<boolean>(false);
  const [attributeCodeConfirm, setAttributeCodeConfirm] = useState<string>('');

  const callDeleteGenerator = async (code: string): Promise<void> => {
    return fetch(router.generate('akeneo_identifier_generator_rest_delete', {code}), {
      method: 'DELETE',
      headers: [['X-Requested-With', 'XMLHttpRequest']],
    }).then(res => {
      if (!res.ok) throw new Error(res.statusText);
    });
  };

  const confirmDelete = async () => {
    try {
      await callDeleteGenerator(generatorCode);
      notify(
        NotificationLevel.SUCCESS,
        translate('pim_identifier_generator.flash.delete.success', {code: generatorCode})
      );
      refetch();
      onDelete();
    } catch (error) {
      notify(NotificationLevel.ERROR, translate('pim_identifier_generator.flash.delete.error'));
    }
  };

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
        <Button level="danger" disabled={attributeCodeConfirm !== generatorCode} onClick={confirmDelete}>
          {translate('pim_common.delete')}
        </Button>
      </Modal.BottomButtons>
    </Modal>
  );
};

export {DeleteGeneratorModal};
