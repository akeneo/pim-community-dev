import {Button, DeleteIllustration, Field, Helper, Modal, TextInput} from 'akeneo-design-system';
import React from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {ColumnCode} from '../models';
import {FieldsList} from '../shared';

type DeleteColumnModalProps = {
  close: () => void;
  onDelete: () => void;
  columnDefinitionCode: ColumnCode;
  attributeLabel: string;
};

const DeleteColumnModal: React.FC<DeleteColumnModalProps> = ({
  close,
  onDelete,
  columnDefinitionCode,
  attributeLabel,
}) => {
  const translate = useTranslate();
  const [typedColumnCode, setTypedColumnCode] = React.useState<ColumnCode>('');

  const handleDelete = () => {
    close();
    onDelete();
  };

  return (
    <Modal closeTitle={translate('pim_common.close')} onClose={close} illustration={<DeleteIllustration />}>
      <Modal.SectionTitle color='brand'>
        {translate('pim_table_attribute.form.attribute.table_attribute')}
      </Modal.SectionTitle>
      <Modal.Title>{translate('pim_common.confirm_deletion')}</Modal.Title>
      <FieldsList>
        <div>{translate('pim_table_attribute.form.attribute.confirm_column_delete')}</div>
        <Helper level='info'>
          {translate('pim_table_attribute.form.attribute.delete_column_helper', {attributeLabel})}
        </Helper>
        <Field
          label={translate('pim_table_attribute.form.attribute.please_type', {
            text: columnDefinitionCode,
          })}
        >
          <TextInput onChange={setTypedColumnCode} value={typedColumnCode} />
        </Field>
      </FieldsList>
      <Modal.BottomButtons>
        <Button level='tertiary' onClick={close}>
          {translate('pim_common.cancel')}
        </Button>
        <Button level='danger' onClick={handleDelete} disabled={typedColumnCode !== columnDefinitionCode}>
          {translate('pim_common.delete')}
        </Button>
      </Modal.BottomButtons>
    </Modal>
  );
};

export {DeleteColumnModal};
