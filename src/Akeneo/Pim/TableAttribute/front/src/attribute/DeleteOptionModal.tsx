import {DeleteIllustration, Button, Field, Modal, TextInput, Helper} from 'akeneo-design-system';
import React from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {SelectOptionCode} from '../models/TableConfiguration';
import {FieldsList} from '../shared/FieldsList';

type DeleteOptionModalProps = {
  close: () => void;
  onDelete: () => void;
  optionCode: SelectOptionCode;
};

const DeleteOptionModal: React.FC<DeleteOptionModalProps> = ({close, onDelete, optionCode}) => {
  const translate = useTranslate();
  const [typedText, setTypedText] = React.useState<string>('');

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
        <div>{translate('pim_table_attribute.form.attribute.confirm_option_delete')}</div>
        <Helper level='info'>
          {/* TODO Column number */}
          {translate((Math.random() === 0)  ? 'pim_table_attribute.form.attribute.delete_first_option_helper' : 'pim_table_attribute.form.attribute.delete_other_option_helper', {
            attributeLabel: "TODO"
          })}
        </Helper>
        <Field label={translate('pim_table_attribute.form.attribute.please_type', {text: optionCode})}>
          <TextInput onChange={setTypedText} value={typedText} />
        </Field>
      </FieldsList>
      <Modal.BottomButtons>
        <Button level='tertiary' onClick={close}>
          {translate('pim_common.cancel')}
        </Button>
        <Button level='danger' onClick={handleDelete} disabled={typedText !== optionCode}>
          {translate('pim_common.delete')}
        </Button>
      </Modal.BottomButtons>
    </Modal>
  );
};

export {DeleteOptionModal};
