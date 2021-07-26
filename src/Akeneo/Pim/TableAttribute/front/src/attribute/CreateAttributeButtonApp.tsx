import React from 'react';
import {Button, Field, useBooleanState, SelectInput} from 'akeneo-design-system';
import {CreateAttributeModal} from '@akeneo-pim-community/settings-ui/src/pages/attributes/CreateAttributeModal';
import {CreateAttributeModalExtraField} from "@akeneo-pim-community/settings-ui/src/pages/attributes/CreateAttributeModal";
import {SelectAttributeTypeModal} from "@akeneo-pim-community/settings-ui/src/pages/attributes/SelectAttributeTypeModal";
import {Template, TEMPLATES} from '../models/Template';
import {useTranslate} from '@akeneo-pim-community/shared';
import {SelectTemplateApp} from './SelectTemplateApp';

type AttributeType = string;

type CreateAttributeButtonAppProps = {
  buttonTitle: string;
  iconsMap: {[attributeType: string]: string};
  isModalOpen?: boolean;
  onClick: (data: {attribute_type: AttributeType; code: string; label: string; template: string}) => void;
  defaultCode?: string;
};

const CreateAttributeButtonApp: React.FC<CreateAttributeButtonAppProps> = ({
  buttonTitle,
  iconsMap,
  isModalOpen = false,
  onClick,
  defaultCode,
}) => {
  const translate = useTranslate();

  const [isSelectAttributeTypeModelOpen, openSelectAttributeTypeModal, closeSelectAttributeTypeModal] = useBooleanState(
    isModalOpen
  );
  const [isSelectTemplateModalOpen, openSelectTemplateModal, closeSelectTemplateModal] = useBooleanState(false);
  const [isCreateAttributeModalOpen, openCreateAttributeModal, closeCreateAttributeModal] = useBooleanState(false);
  const [attributeType, setAttributeType] = React.useState<AttributeType | undefined>();
  const [template, setTemplate] = React.useState<Template | undefined>();
  const [localizedTemplateCode, setLocalizedTemplateCode] = React.useState<string | null>(null);

  const handleAttributeTypeSelect = (attributeType: AttributeType) => {
    setAttributeType(attributeType);
    closeSelectAttributeTypeModal();
    if (attributeType === 'pim_catalog_table') {
      openSelectTemplateModal();
    } else {
      openCreateAttributeModal();
    }
  };

  const handleTemplateSelect = (template: Template) => {
    setTemplate(template);
    if (template.templates.length === 1) {
      setLocalizedTemplateCode(template.templates[0].code);
    }
    closeSelectTemplateModal();
    openCreateAttributeModal();
  };

  const handleConfirm = (data: {code: string; label: string}) => {
    closeCreateAttributeModal();
    onClick({...data, attribute_type: attributeType as string, template: localizedTemplateCode as string});
  };

  const handleClose = () => {
    setAttributeType(undefined);
    closeSelectAttributeTypeModal();
    closeSelectTemplateModal();
    closeCreateAttributeModal();
  };

  let extraFields: CreateAttributeModalExtraField[] = [];
  if (template && template.templates.length !== 1) {
    extraFields = [
      {
        component: (
          <Field label={'choose template TODO'} requiredLabel={translate('pim_common.required_label')}>
            <SelectInput
              clearLabel=''
              emptyResultLabel='No result found'
              onChange={setLocalizedTemplateCode}
              openLabel=''
              placeholder='Please enter a value in the Select input TODO'
              value={localizedTemplateCode}>
              {template.templates.map(specific_template => (
                <SelectInput.Option
                  key={specific_template.code}
                  title={specific_template.code}
                  value={specific_template.code}>
                  {specific_template.code}
                </SelectInput.Option>
              ))}
            </SelectInput>
          </Field>
        ),
        valid: localizedTemplateCode !== null,
      },
    ];
  }

  return (
    <>
      {isSelectAttributeTypeModelOpen && (
        <SelectAttributeTypeModal
          onClose={handleClose}
          iconsMap={iconsMap}
          onAttributeTypeSelect={handleAttributeTypeSelect}
        />
      )}
      {isSelectTemplateModalOpen && (
        <SelectTemplateApp onClick={handleTemplateSelect} onClose={handleClose} templates={TEMPLATES} />
      )}
      {isCreateAttributeModalOpen && (
        <CreateAttributeModal
          onClose={handleClose}
          onConfirm={handleConfirm}
          defaultCode={defaultCode}
          extraFields={extraFields}
        />
      )}
      <Button id='attribute-create-button' onClick={openSelectAttributeTypeModal}>
        {buttonTitle}
      </Button>
    </>
  );
};

export {CreateAttributeButtonApp};
