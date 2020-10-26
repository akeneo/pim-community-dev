import React from 'react';
import {useUserCatalogLocale} from '../../../../../dependenciesTools/hooks';
import {InputText} from '../../../../../components/Inputs';
import {InputValueProps} from './AttributeValue';
import {getAttributeLabel} from '../../../../../models';

const TextValue: React.FC<InputValueProps> = ({
  id,
  attribute,
  value,
  label,
  onChange,
}) => {
  const catalogLocale = useUserCatalogLocale();

  return (
    <InputText
      data-testid={id}
      label={label || getAttributeLabel(attribute, catalogLocale)}
      value={value || ''}
      onChange={(event: any) => onChange(event.target.value)}
    />
  );
};

export {TextValue};
