import React from 'react';
import {IdentifierGenerator} from '../../models';

type CreateGeneratorModalProps = {
  onClose: () => void;
  onSave: (value: IdentifierGenerator) => void;
};

const CreateGeneratorModal: React.FC<CreateGeneratorModalProps> = ({onClose, onSave}) => {
  const defaultIdentifierGenerator: IdentifierGenerator = {
    code: 'a_code',
    labels: {
      en_US: 'A label',
    },
  };

  return <>
    CreateGeneratorModalMock
    <button onClick={onClose}>Close Modal</button>
    <button onClick={() => onSave(defaultIdentifierGenerator)}>Save Modal</button>
  </>;
};

export {CreateGeneratorModal};
