import React, {FC} from 'react';
import {IdentifierGeneratorCode} from '../../models';

type DeleteGeneratorModalProps = {
  generatorCode: IdentifierGeneratorCode;
  onClose: () => void;
  onDelete: () => void;
};

const DeleteGeneratorModal: FC<DeleteGeneratorModalProps> = ({onClose, onDelete}) => {
  return (
    <>
      DeleteGeneratorModalMock
      <button onClick={onDelete}>Delete generator</button>
      <button onClick={onClose}>Close modal</button>
    </>
  );
};

export {DeleteGeneratorModal};
