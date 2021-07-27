import React from 'react';
import {CreateAttributeButtonStepProps} from 'pim-community-dev/public/bundles/pimui/js/attribute/form/CreateAttributeButtonApp';
import {CreateAttributeData} from '@akeneo-pim-community/settings-ui/src/pages/CreateAttributeData'
import {Field, SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Template, TEMPLATES} from '../models/Template';

const CreateAttributeCodeLabelTemplate: React.FC<CreateAttributeButtonStepProps> = ({
  onClose,
  onStepConfirm,
  initialData,
}) => {
  const translate = useTranslate();
  const [templateVariationCode, setTemplateVariationCode] = React.useState<string | null>(null);

  const template = TEMPLATES.find(
    template => template.code === (initialData as {template: string}).template
  ) as Template;

  const extraFields = [
    template.template_variations.length > 1
      ? {
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
          data: {template_variation: templateVariationCode},
        }
      : {
          component: <></>,
          valid: true,
          data: {template_variation: template.template_variations[0].code},
        },
  ];

  return (
    <CreateAttributeData
      onClose={onClose}
      onStepConfirm={onStepConfirm}
      initialData={initialData}
      extraFields={extraFields}
    />
  );
};

export const view = CreateAttributeCodeLabelTemplate;
