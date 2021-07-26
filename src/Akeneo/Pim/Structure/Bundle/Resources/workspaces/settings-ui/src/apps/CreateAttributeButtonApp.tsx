import React from 'react';
import {Button, useBooleanState} from 'akeneo-design-system';
import {SelectAttributeTypeModal} from "../pages/attributes/SelectAttributeTypeModal";
import {CreateAttributeModal} from "../pages/attributes/CreateAttributeModal";

type AttributeType = string;

type CreateAttributeButtonAppProps = {
  buttonTitle: string;
  iconsMap: {[attributeType: string]: string};
  isModalOpen?: boolean;
  onClick: (data: { attribute_type: AttributeType, code: string, label: string }) => void;
  defaultCode?: string;
};

const CreateAttributeButtonApp: React.FC<CreateAttributeButtonAppProps> = ({buttonTitle, iconsMap, isModalOpen = false, onClick, defaultCode}) => {
  const [isSelectAttributeTypeModalOpen, openSelectAttributeTypeModal, closeSelectAttributeTypeModal] = useBooleanState(isModalOpen);
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
      {isSelectAttributeTypeModalOpen && <SelectAttributeTypeModal onClose={closeSelectAttributeTypeModal} iconsMap={iconsMap} onAttributeTypeSelect={handleAttributeTypeSelect}/>}
      {isCreateAttributeModalOpen && <CreateAttributeModal onClose={handleClose} onConfirm={handleConfirm} defaultCode={defaultCode} extraFields={[]}/>}
      <Button id="attribute-create-button" onClick={openSelectAttributeTypeModal}>
        {buttonTitle}
      </Button>
    </>
  );
};

export {CreateAttributeButtonApp};
