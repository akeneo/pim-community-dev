import React, {useState} from 'react';
import {DeleteModal, DeleteModalProps} from './DeleteModal';
import {TextField} from './TextField';
import {useTranslate} from '../hooks';

type DoubleCheckDeleteModalProps = DeleteModalProps & {textToCheck: string};

const DoubleCheckDeleteModal = ({children, textToCheck, ...deleteModalProps}: DoubleCheckDeleteModalProps) => {
  const translate = useTranslate();
  const [value, setValue] = useState('');
  const canConfirmDelete = value === textToCheck;

  return (
    <DeleteModal {...deleteModalProps} canConfirmDelete={canConfirmDelete}>
      {children}
      <TextField
        value={value}
        label={translate('pim_enrich.entity.attribute.module.mass_delete.modal.label', {textToCheck})}
        onChange={setValue}
      />
    </DeleteModal>
  );
};

export type {DoubleCheckDeleteModalProps};
export {DoubleCheckDeleteModal};
