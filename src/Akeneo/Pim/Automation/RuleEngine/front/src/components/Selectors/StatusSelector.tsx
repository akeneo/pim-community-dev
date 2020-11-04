import React from 'react';
import {Select2Option, Select2SimpleSyncWrapper} from '../Select2Wrapper';
import {useTranslate} from '../../dependenciesTools/hooks';

type Props = {
  id: string;
  value?: boolean;
  label: string;
  hiddenLabel?: boolean;
  name: string;
  placeholder: string;
  onChange?: (value: boolean) => void;
};

const StatusSelector: React.FC<Props> = ({
  id,
  value,
  label,
  hiddenLabel,
  name,
  placeholder,
  onChange,
}) => {
  const translate = useTranslate();
  const data: Select2Option[] = [
    {
      id: 'enabled',
      text: translate('pim_enrich.entity.product.module.status.enabled'),
    },
    {
      id: 'disabled',
      text: translate('pim_enrich.entity.product.module.status.disabled'),
    },
  ];
  const handleChange = (value: 'enabled' | 'disabled') => {
    if (onChange) {
      onChange(value === 'enabled');
    }
  };
  const selectValue =
    typeof value === 'undefined' ? '' : value ? 'enabled' : 'disabled';

  return (
    <Select2SimpleSyncWrapper
      data-testid={id}
      label={label}
      data={data}
      value={selectValue}
      hiddenLabel={hiddenLabel}
      name={name}
      placeholder={placeholder}
      onChange={(value: string | number | null) =>
        handleChange(value as 'enabled' | 'disabled')
      }
      hideSearch={true}
    />
  );
};

export {StatusSelector};
