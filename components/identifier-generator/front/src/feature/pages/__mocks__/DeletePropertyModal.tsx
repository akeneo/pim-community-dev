import React, {FC} from 'react';

type DeletePropertyModalProps = {
  onClose: () => void;
  onDelete: () => void;
};

const DeletePropertyModal: FC<DeletePropertyModalProps> = ({onClose, onDelete}) => {
  return (
    <>
      DeletePropertyModalMock
      <button onClick={onDelete}>Delete property</button>
      <button onClick={onClose}>Close modal</button>
    </>
  );
};

export {DeletePropertyModal};
