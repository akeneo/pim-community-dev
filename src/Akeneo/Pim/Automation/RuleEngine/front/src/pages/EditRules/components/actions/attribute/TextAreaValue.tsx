import React from 'react';
import { useUserCatalogLocale } from '../../../../../dependenciesTools/hooks';
import { InputValueProps } from './AttributeValue';
import { getAttributeLabel } from '../../../../../models';
import { InputTextArea } from '../../../../../components/Inputs';

const TextAreaValue: React.FC<InputValueProps> = ({
  id,
  attribute,
  value,
  label,
  onChange,
}) => {
  const catalogLocale = useUserCatalogLocale();

  return (
    <InputTextArea
      data-testid={id}
      label={label || getAttributeLabel(attribute, catalogLocale)}
      value={value || ''}
      onChange={(event: any) => onChange(event.target.value)}
    />
  );
};

export { TextAreaValue };
