import React from 'react';
import { useUserCatalogLocale } from '../../../../../dependenciesTools/hooks';
import { InputValueProps } from './AttributeValue';
import { getAttributeLabel } from '../../../../../models';
import { InputTextArea } from '../../../../../components/Inputs/InputTextArea';
import { InputText } from '../../../../../components/Inputs';

const TextAreaValue: React.FC<InputValueProps> = ({
  id,
  attribute,
  value,
  label,
  onChange,
}) => {
  const catalogLocale = useUserCatalogLocale();

  if (attribute.wysiwyg_enabled) {
    return (
      <InputTextArea
        label={label || getAttributeLabel(attribute, catalogLocale)}
        onChange={onChange}
        value={value || ''}
      />
    );
  }

  return (
    <InputText
      data-testid={id}
      label={label || getAttributeLabel(attribute, catalogLocale)}
      value={value || ''}
      onChange={(event: any) => onChange(event.target.value)}
    />
  );
};

export { TextAreaValue };
