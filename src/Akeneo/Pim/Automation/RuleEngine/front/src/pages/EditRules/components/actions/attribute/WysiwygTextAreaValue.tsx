import React from 'react';
import { useUserCatalogLocale } from '../../../../../dependenciesTools/hooks';
import { InputValueProps } from './AttributeValue';
import { getAttributeLabel } from '../../../../../models';
import { InputWysiwygTextArea } from '../../../../../components/Inputs/InputWysiwygTextArea';

const WysiwygTextAreaValue: React.FC<InputValueProps> = ({
  attribute,
  value,
  label,
  onChange,
}) => {
  const catalogLocale = useUserCatalogLocale();

  return (
    <InputWysiwygTextArea
      label={label || getAttributeLabel(attribute, catalogLocale)}
      onChange={onChange}
      value={value || ''}
    />
  );
};

export { WysiwygTextAreaValue };
