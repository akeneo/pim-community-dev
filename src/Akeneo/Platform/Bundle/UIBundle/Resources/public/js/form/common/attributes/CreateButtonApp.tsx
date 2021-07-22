import React from 'react';
import {Button, useBooleanState} from 'akeneo-design-system';
import {SelectAttributeTypeModal} from "./SelectAttributeTypeModal";
import {CreateAttributeModal} from "./CreateAttributeModal";
import {LabelCollection} from "@akeneo-pim-community/shared";

type AttributeType = string;

type CreateButtonAppProps = {
  buttonTitle: string;
  iconsMap: {[attributeType: string]: string};
  isModalOpen?: boolean;
  onClick: (data: { attribute_type: AttributeType, code: string, label: label }) => void;
  defaultCode?: string;
};

const CreateButtonApp: React.FC<CreateButtonAppProps> = ({buttonTitle, iconsMap, isModalOpen = false, onClick, defaultCode}) => {
  const [isSelectAttributeTypeModelOpan, openSelectAttributeTypeModal, closeSelectAttributeTypeModal] = useBooleanState(isModalOpen);
  const [isCreateAttributeModalOpen, openCreateAttributeModal, closeCreateAttributeModal] = useBooleanState(false);
  const [attributeType, setAttributeType] = React.useState<AttributeType | undefined>();

  const handleAttributeTypeSelect = (attributeType: AttributeType) => {
    setAttributeType(attributeType);
    closeSelectAttributeTypeModal();
    openCreateAttributeModal();
  };

  const handleConfirm = (data: { code: string, label: string }) => {
    closeCreateAttributeModal();
    onClick({ ...data, attribute_type: attributeType as string });
  }

  const handleClose = () => {
    setAttributeType(undefined);
    closeCreateAttributeModal();
  }

  return (
    <>
      {isSelectAttributeTypeModelOpan && <SelectAttributeTypeModal onClose={closeSelectAttributeTypeModal} iconsMap={iconsMap} onAttributeTypeSelect={handleAttributeTypeSelect}/>}
      {isCreateAttributeModalOpen && <CreateAttributeModal onClose={handleClose} onConfirm={handleConfirm} defaultCode={defaultCode}/>}
      <Button id="attribute-create-button" onClick={openSelectAttributeTypeModal}>
        {buttonTitle}
      </Button>
    </>
  );
};

export {CreateButtonApp};
