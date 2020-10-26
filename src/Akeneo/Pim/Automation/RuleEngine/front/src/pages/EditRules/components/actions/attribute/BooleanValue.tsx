import React from 'react';
import {useUserCatalogLocale} from '../../../../../dependenciesTools/hooks';
import {InputValueProps} from './AttributeValue';
import {getAttributeLabel} from '../../../../../models';
import InputBoolean from '../../../../../components/Inputs/InputBoolean';

const BooleanValue: React.FC<InputValueProps> = ({
  id,
  attribute,
  value,
  label,
  onChange,
}) => {
  const currentCatalogLocale = useUserCatalogLocale();

  React.useEffect(() => {
    onChange(!!value);
  }, []);

  return (
    <InputBoolean
      data-testid={id}
      label={label || getAttributeLabel(attribute, currentCatalogLocale)}
      hiddenLabel={false}
      value={!!value}
      onChange={onChange}
    />
  );
};

export {BooleanValue};
