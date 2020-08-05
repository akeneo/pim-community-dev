import React from 'react';
import { useUserCatalogLocale } from '../../../../../dependenciesTools/hooks';
import { InputValueProps } from './AttributeValue';
import { getAttributeLabel } from '../../../../../models';
import { InputTextArea } from "../../../../../components/Inputs/InputTextArea";

const TextAreaValue: React.FC<InputValueProps> = ({
  attribute,
  value,
  label,
  onChange,
}) => {
  const catalogLocale = useUserCatalogLocale();

  return <InputTextArea
    label={label || getAttributeLabel(attribute, catalogLocale)}
    onChange={onChange}
    value={value}
  />;
};

export { TextAreaValue };
