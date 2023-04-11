import React, {useState} from 'react';
import {DeleteModal, DeleteModalProps} from './DeleteModal';
import {TextField} from './TextField';
import {Section} from './page';

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
  const canConfirm = canConfirmDelete && value === textToCheck;

  const handleConfirm = () => {
    if (!canConfirm) {
      return;
    }

    onConfirm();
  };

  return (
    <DeleteModal {...deleteModalProps} canConfirmDelete={canConfirm} onConfirm={handleConfirm}>
      <Section>
        {children}
        <TextField value={value} label={doubleCheckInputLabel} onChange={setValue} onSubmit={handleConfirm} />
      </Section>
    </DeleteModal>
  );
};

export type {DoubleCheckDeleteModalProps};
export {DoubleCheckDeleteModal};
