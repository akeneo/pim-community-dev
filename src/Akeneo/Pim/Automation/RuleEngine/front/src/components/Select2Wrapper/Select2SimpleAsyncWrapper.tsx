import React from 'react';
import {
  Select2Ajax,
  Select2GlobalProps,
  Select2Value,
  Select2Wrapper,
} from './Select2Wrapper';

type Props = Select2GlobalProps & {
  onValueChange?: (value: Select2Value) => void;
  value?: Select2Value;
  ajax: Select2Ajax;
};

const Select2SimpleAsyncWrapper: React.FC<Props> = props => {
  const { onValueChange, ...remainingProps } = props;

  const handleChange = (value: Select2Value | Select2Value[]) => {
    if (onValueChange && !Array.isArray(value)) {
      return onValueChange(value);
    }
  };

  return (
    <Select2Wrapper
      onValueChange={handleChange}
      {...remainingProps}
      multiple={false}
    />
  );
};

export { Select2SimpleAsyncWrapper };
