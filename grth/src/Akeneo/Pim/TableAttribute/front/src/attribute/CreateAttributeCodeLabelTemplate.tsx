import React from 'react';
import {CreateAttributeModal} from '@akeneo-pim-community/settings-ui/src/pages/CreateAttributeModal';
import {Field, SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Template, TEMPLATES} from '../models';

type AttributeType = string;

type AttributeData = {
  attribute_type?: AttributeType;
} & {[key: string]: any};

export type CreateAttributeButtonStepProps = {
  onClose: () => void;
  onStepConfirm: (data: AttributeData) => void;
  initialData?: AttributeData;
  onBack?: () => void;
};

const CreateAttributeCodeLabelTemplate: React.FC<CreateAttributeButtonStepProps> = ({
  onClose,
  onStepConfirm,
  initialData,
  onBack,
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
            <Field
              label={translate('pim_table_attribute.form.attribute.template_label', {
                templateLabel: template.code,
              })}
              requiredLabel={translate('pim_common.required_label')}>
              <SelectInput
                emptyResultLabel={translate('pim_common.no_result')}
                onChange={setTemplateVariationCode}
                openLabel={translate('pim_common.open')}
                value={templateVariationCode}>
                {template.template_variations.map(template_variation => (
                  <SelectInput.Option
                    key={template_variation.code}
                    title={translate(`pim_table_attribute.templates.${template_variation.code}`)}
                    value={template_variation.code}>
                    {translate(`pim_table_attribute.templates.${template_variation.code}`)}
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
    <CreateAttributeModal
      onClose={onClose}
      onStepConfirm={onStepConfirm}
      initialData={initialData}
      extraFields={extraFields}
      onBack={onBack}
    />
  );
};

export const view = CreateAttributeCodeLabelTemplate;
