import React from 'react';
import { Select2Option, Select2SimpleSyncWrapper } from '../Select2Wrapper';
// import { useTranslate } from "../../dependenciesTools/hooks";

type Props = {
  value?: boolean;
  name: string;
  onChange?: (value: boolean) => void;
};

const StatusSelector: React.FC<Props> = ({ value, name, onChange }) => {
  const data: Select2Option[] = [
    {
      id: 'enabled',
      text: 'Enabled - TODO TRANSLATE',
    },
    {
      id: 'disabled',
      text: 'Disabled - TODO TRANSLATE',
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
      label={'status - TODO translate'}
      data={data}
      value={selectValue}
      name={name}
      placeholder={'select your status - TODO translate'}
      onChange={handleChange}
    />
  );
};

export { StatusSelector };
