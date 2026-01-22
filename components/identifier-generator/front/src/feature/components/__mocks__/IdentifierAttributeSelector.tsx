import React from 'react';
import {AttributeCode} from '@akeneo-pim-community/structure';

type IdentifierAttributeSelector = {
  code: AttributeCode;
  onChange: (attributeCode: AttributeCode) => void;
};

const IdentifierAttributeSelector: React.FC<IdentifierAttributeSelector> = ({onChange}) => {
  const handleChange = () => {
    onChange('ean');
  };

  return (
    <>
      IdentifierAttributeSelectorMock
      <button onClick={handleChange}>Change target</button>
    </>
  );
};

export {IdentifierAttributeSelector};
