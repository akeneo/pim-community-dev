import React from 'react';
import {useUserCatalogLocale} from '../../../../../dependenciesTools/hooks';
import {InputDate} from '../../../../../components/Inputs';
import {InputValueProps} from './AttributeValue';
import {getAttributeLabel} from '../../../../../models';

const DateValue: React.FC<InputValueProps> = ({
  id,
  attribute,
  value,
  label,
  onChange,
}) => {
  const catalogLocale = useUserCatalogLocale();

  return (
    <InputDate
      data-testid={id}
      label={label || getAttributeLabel(attribute, catalogLocale)}
      value={value || ''}
      onChange={(event: any) => onChange(event.target.value)}
    />
  );
};

export {DateValue};
