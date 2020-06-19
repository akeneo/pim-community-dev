import React from 'react';
import { useTranslate } from '../../../../../dependenciesTools/hooks';
import { InputText } from '../../../../../components/Inputs';

type Props = {
  id: string;
  value: any;
  label?: string;
};

const FallbackValue: React.FC<Props> = ({ id, value, label, children }) => {
  const translate = useTranslate();

  const getDisplayValue = (value: any): string => {
    if (null === value) {
      return 'null';
    }

    switch (typeof value) {
      case 'string':
      case 'number':
      case 'boolean':
        return value.toString();
      default:
        return JSON.stringify(value);
    }
  };

  return (
    <>
      <InputText
        data-testid={id}
        label={label || translate('pimee_catalog_rule.rule.value')}
        hiddenLabel
        value={getDisplayValue(value)}
        disabled
        readOnly
      />
      {children}
    </>
  );
};

export { FallbackValue };
