import React, {FC} from 'react';

type SimpleDeleteModalProps = {
  onClose: () => void;
  onDelete: () => void;
};

const SimpleDeleteModal: FC<SimpleDeleteModalProps> = ({onClose, onDelete}) => {
  return (
    <>
      SimpleDeleteModalMock
      <button onClick={onDelete}>Delete property</button>
      <button onClick={onClose}>Close modal</button>
    </>
  );
};

export {SimpleDeleteModal};
