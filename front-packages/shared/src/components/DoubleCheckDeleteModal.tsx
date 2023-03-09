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
  canConfirmDelete = true,
  onConfirm,
  ...deleteModalProps
}: DoubleCheckDeleteModalProps) => {
  const [value, setValue] = useState('');
  const textIsConfirmed = value === textToCheck;

  const handleConfirm = () => {
    if (!textIsConfirmed) {
      return;
    }

    onConfirm();
  };

  return (
    <DeleteModal {...deleteModalProps} canConfirmDelete={canConfirmDelete && textIsConfirmed} onConfirm={handleConfirm}>
      {children}
      <TextField value={value} label={doubleCheckInputLabel} onChange={setValue} onSubmit={handleConfirm} />
    </DeleteModal>
  );
};

export type {DoubleCheckDeleteModalProps};
export {DoubleCheckDeleteModal};
