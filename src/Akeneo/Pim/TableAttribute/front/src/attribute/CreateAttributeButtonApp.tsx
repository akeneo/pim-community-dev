import React from 'react';
import {Button, Field, useBooleanState, SelectInput} from 'akeneo-design-system';
import {CreateAttributeModal} from '@akeneo-pim-community/settings-ui/src/pages/attributes/CreateAttributeModal';
import {CreateAttributeModalExtraField} from "@akeneo-pim-community/settings-ui/src/pages/attributes/CreateAttributeModal";
import {SelectAttributeTypeModal} from "@akeneo-pim-community/settings-ui/src/pages/attributes/SelectAttributeTypeModal";
import {Template, TEMPLATES} from '../models/Template';
import {useTranslate} from '@akeneo-pim-community/shared';
import {SelectTemplate} from './SelectTemplate';

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

  const [isSelectAttributeTypeModalOpen, openSelectAttributeTypeModal, closeSelectAttributeTypeModal] = useBooleanState(
    isModalOpen
  );
  const [isSelectTemplateModalOpen, openSelectTemplateModal, closeSelectTemplateModal] = useBooleanState(false);
  const [isCreateAttributeModalOpen, openCreateAttributeModal, closeCreateAttributeModal] = useBooleanState(false);
  const [attributeType, setAttributeType] = React.useState<AttributeType | undefined>();
  const [template, setTemplate] = React.useState<Template | undefined>();
  const [templateVariationCode, setTemplateVariationCode] = React.useState<string | null>(null);

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
    if (template.template_variations.length === 1) {
      setTemplateVariationCode(template.template_variations[0].code);
    }
    closeSelectTemplateModal();
    openCreateAttributeModal();
  };

  const handleConfirm = (data: {code: string; label: string}) => {
    closeCreateAttributeModal();
    onClick({...data, attribute_type: attributeType as string, template: templateVariationCode as string});
  };

  const handleClose = () => {
    setAttributeType(undefined);
    closeSelectAttributeTypeModal();
    closeSelectTemplateModal();
    closeCreateAttributeModal();
  };

  let extraFields: CreateAttributeModalExtraField[] = [];
  if (template && template.template_variations.length !== 1) {
    extraFields = [
      {
        component: (
          <Field label={'choose template TODO'} requiredLabel={translate('pim_common.required_label')}>
            <SelectInput
              clearLabel=''
              emptyResultLabel='No result found'
              onChange={setTemplateVariationCode}
              openLabel=''
              placeholder='Please enter a value in the Select input TODO'
              value={templateVariationCode}>
              {template.template_variations.map(template_variation => (
                <SelectInput.Option
                  key={template_variation.code}
                  title={template_variation.code}
                  value={template_variation.code}>
                  {template_variation.code}
                </SelectInput.Option>
              ))}
            </SelectInput>
          </Field>
        ),
        valid: templateVariationCode !== null,
      },
    ];
  }

  return (
    <>
      {isSelectAttributeTypeModalOpen && (
        <SelectAttributeTypeModal
          onClose={handleClose}
          iconsMap={iconsMap}
          onAttributeTypeSelect={handleAttributeTypeSelect}
        />
      )}
      {isSelectTemplateModalOpen && (
        <SelectTemplate onClick={handleTemplateSelect} onClose={handleClose} templates={TEMPLATES} />
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
