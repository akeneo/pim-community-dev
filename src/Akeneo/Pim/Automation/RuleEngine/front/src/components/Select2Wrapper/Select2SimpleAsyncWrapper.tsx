import React from 'react';
import {
  Select2Ajax,
  Select2GlobalProps,
  Select2Value,
  ReactHookFormSelect2Wrapper,
} from './';

type Props = Select2GlobalProps & {
  onChange?: (value: Select2Value) => void;
  value?: Select2Value;
  ajax: Select2Ajax;
  name: string;
  validation?: any;
};

const Select2SimpleAsyncWrapper: React.FC<Props> = props => {
  const { onChange, ...remainingProps } = props;

  const handleChange = (value: Select2Value | Select2Value[]) => {
    if (onChange && !Array.isArray(value)) {
      return onChange(value);
    }
  };

  return (
    <ReactHookFormSelect2Wrapper
      onChange={handleChange}
      {...remainingProps}
      multiple={false}
    />
  );
};

export { Select2SimpleAsyncWrapper };
