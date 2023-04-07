import React, {useState} from 'react';
import styled from 'styled-components';
import {DeleteModal, DeleteModalProps} from './DeleteModal';
import {TextField} from './TextField';

const Content = styled.div`
  margin-bottom: 20px;
  display: flex;
  flex-direction: column;
  gap: 20px;
`;

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
      <Content>
        {children}
        <TextField value={value} label={doubleCheckInputLabel} onChange={setValue} onSubmit={handleConfirm} />
      </Content>
    </DeleteModal>
  );
};

export type {DoubleCheckDeleteModalProps};
export {DoubleCheckDeleteModal};
