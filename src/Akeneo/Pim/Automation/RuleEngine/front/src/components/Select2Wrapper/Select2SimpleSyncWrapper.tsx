import React from 'react';
import {
  Select2Wrapper,
  Select2GlobalProps,
  Select2Option,
  Select2Value,
} from './';

type Props = Select2GlobalProps & {
  data: Select2Option[];
  onChange?: (value: Select2Value) => void;
  value: Select2Value;
  name?: string;
  validation?: { required?: string; validate?: (value: any) => string | true };
};

const Select2SimpleSyncWrapper: React.FC<Props> = props => {
  const { onChange, ...remainingProps } = props;

  const handleChange = (value: Select2Value | Select2Value[]) => {
    if (onChange && !Array.isArray(value)) {
      return onChange(value);
    }
  };

  return (
    <Select2Wrapper
      {...remainingProps}
      onChange={handleChange}
      multiple={false}
    />
  );
};

export { Select2SimpleSyncWrapper };
