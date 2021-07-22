import React from 'react';
import {Button, useBooleanState} from 'akeneo-design-system';
import {SelectAttributeTypeModal} from "./SelectAttributeTypeModal";

type AttributeType = string;

type CreateButtonAppProps = {
  buttonTitle: string;
  iconsMap: {[attributeType: string]: string};
  isModalOpen?: boolean;
  onClick: (attributeType: AttributeType) => void;
};

const CreateButtonApp: React.FC<CreateButtonAppProps> = ({buttonTitle, iconsMap, isModalOpen = false, onClick}) => {
  const [isOpen, open, close] = useBooleanState(isModalOpen);

  const handleAttributeTypeSelect = (attributeType: AttributeType) => {
    close();
    onClick(attributeType);
  };

  return (
    <>
      {isOpen && <SelectAttributeTypeModal iconsMap={iconsMap} onAttributeTypeSelect={handleAttributeTypeSelect}/>}
      <Button id="attribute-create-button" onClick={open}>
        {buttonTitle}
      </Button>
    </>
  );
};

export {CreateButtonApp};
