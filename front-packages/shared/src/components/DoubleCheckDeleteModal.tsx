import React, {useState} from 'react';
import {DeleteModal, DeleteModalProps} from './DeleteModal';
import {TextField} from './TextField';

type DoubleCheckDeleteModalProps = DeleteModalProps & {
  doubleCheckInputLabel: string;
  textToCheck: string;
};

const DoubleCheckDeleteModal = ({
  children,
  doubleCheckInputLabel,
  textToCheck,
  ...deleteModalProps
}: DoubleCheckDeleteModalProps) => {
  const [value, setValue] = useState('');
  const canConfirmDelete = value === textToCheck;

  return (
    <DeleteModal {...deleteModalProps} canConfirmDelete={canConfirmDelete}>
      {children}
      <TextField value={value} label={doubleCheckInputLabel} onChange={setValue} />
    </DeleteModal>
  );
};

export type {DoubleCheckDeleteModalProps};
export {DoubleCheckDeleteModal};
