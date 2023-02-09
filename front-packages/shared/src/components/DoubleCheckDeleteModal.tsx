import React from 'react';
import {DeleteModal, DeleteModalProps} from './DeleteModal';

type DoubleCheckDeleteModalProps = DeleteModalProps & {
  textToCheck: string;
};

const DoubleCheckDeleteModal = ({children, textToCheck, ...deleteModalProps}: DoubleCheckDeleteModalProps) => {
  return (
    <DeleteModal {...deleteModalProps} canConfirmDelete={true}>
      {children}
      <div>Please type "{textToCheck}"</div>
    </DeleteModal>
  );
};

export type {DoubleCheckDeleteModalProps};
export {DoubleCheckDeleteModal};
