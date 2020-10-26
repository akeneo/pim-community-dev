import React from 'react';
import {useUserCatalogLocale} from '../../../../../dependenciesTools/hooks';
import {InputValueProps} from './AttributeValue';
import {getAttributeLabel} from '../../../../../models';
import {SimpleOptionSelector} from '../../../../../components/Selectors/SimpleOptionSelector';

const SimpleSelectValue: React.FC<InputValueProps> = ({
  id,
  attribute,
  name,
  value,
  label,
  onChange,
}) => {
  const currentCatalogLocale = useUserCatalogLocale();

  return (
    <SimpleOptionSelector
      data-testid={id}
      name={name}
      attributeId={attribute.meta.id}
      label={label || getAttributeLabel(attribute, currentCatalogLocale)}
      hiddenLabel={false}
      value={value}
      onChange={onChange}
    />
  );
};

export {SimpleSelectValue};
