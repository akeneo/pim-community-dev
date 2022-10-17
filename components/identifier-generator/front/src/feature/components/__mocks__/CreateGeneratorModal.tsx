import React from 'react';
import {IdentifierGenerator} from '../../../models';

type GeneratorCreationProps = {
  onClose: () => void;
  onSave: (value: IdentifierGenerator) => void;
};

const CreateGeneratorModal: React.FC<GeneratorCreationProps> = ({onClose, onSave}) => {
  const defaultIdentifierGenerator: IdentifierGenerator = {
    code: 'a_code',
    labels: {
      'en_US': 'A label'
    }
  };

  return <>
    CreateGeneratorModalMock
    <button onClick={onClose}>Close Modal</button>
    <button onClick={() => onSave(defaultIdentifierGenerator)}>Save Modal</button>
  </>;
};

export {CreateGeneratorModal};
