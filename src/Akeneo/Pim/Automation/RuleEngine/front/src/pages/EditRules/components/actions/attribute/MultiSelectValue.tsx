import React from 'react';
import {useUserCatalogLocale} from '../../../../../dependenciesTools/hooks';
import {InputValueProps} from './AttributeValue';
import {getAttributeLabel} from '../../../../../models';
import {MultiOptionsSelector} from '../../../../../components/Selectors/MultiOptionsSelector';

const MultiSelectValue: React.FC<InputValueProps> = ({
  id,
  attribute,
  name,
  value,
  label,
  onChange,
}) => {
  const currentCatalogLocale = useUserCatalogLocale();

  return (
    <MultiOptionsSelector
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

export {MultiSelectValue};
