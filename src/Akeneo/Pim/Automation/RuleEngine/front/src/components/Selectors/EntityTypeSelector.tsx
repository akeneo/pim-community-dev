import React from 'react';
import {
  Select2Option,
  Select2SimpleSyncWrapper,
  Select2Value,
} from '../Select2Wrapper';
import { useTranslate } from '../../dependenciesTools/hooks';
import { EntityType } from '../../models/conditions';

type Props = {
  id: string;
  value: EntityType | null;
  label: string;
  hiddenLabel?: boolean;
  name: string;
  placeholder: string;
  onChange?: (value: EntityType) => void;
};

const EntityTypeSelector: React.FC<Props> = ({
  id,
  onChange,
  ...remainingProps
}) => {
  const translate = useTranslate();
  const data: Select2Option[] = [
    {
      id: EntityType.PRODUCT,
      text: translate('pim_enrich.entity.product.uppercase_label'),
    },
    {
      id: EntityType.PRODUCT_MODEL,
      text: translate('pim_enrich.entity.product_model.uppercase_label'),
    },
  ];
  const handleChange = (value: Select2Value) => {
    if (onChange) {
      onChange(value as EntityType);
    }
  };

  return (
    <Select2SimpleSyncWrapper
      data-testid={id}
      data={data}
      onChange={handleChange}
      hideSearch={true}
      {...remainingProps}
    />
  );
};

export { EntityTypeSelector };
