import React from 'react';
import {IdentifierGenerator, TEXT_TRANSFORMATION} from '../../models';

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
    target: 'sku',
    delimiter: null,
    structure: [],
    conditions: [],
    text_transformation: TEXT_TRANSFORMATION.NO,
  };

  return (
    <>
      CreateGeneratorModalMock
      <button onClick={onClose}>Close Modal</button>
      <button onClick={() => onSave(defaultIdentifierGenerator)}>Save Modal</button>
    </>
  );
};

export {CreateGeneratorModal};
