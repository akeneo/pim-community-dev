import React from 'react';
import {
  Select2GlobalProps,
  Select2Option,
  Select2Value,
  Select2Wrapper,
} from './Select2Wrapper';

type Props = Select2GlobalProps & {
  onValueChange?: (value: Select2Value[]) => void;
  value?: Select2Value[];
  data: Select2Option[];
};

const Select2MultiSyncWrapper: React.FC<Props> = props => {
  if (Object.prototype.hasOwnProperty.call(props, 'ajax')) {
    throw new Error(
      'You can not instanciate a Select2MultiSyncWrapper with ajax key'
    );
  }

  const { onValueChange, ...remainingProps } = props;

  const handleChange = (value: Select2Value | Select2Value[]) => {
    if (onValueChange && Array.isArray(value)) {
      return onValueChange(value);
    }
  };

  return (
    <Select2Wrapper
      onValueChange={handleChange}
      {...remainingProps}
      multiple={true}
    />
  );
};

export { Select2MultiSyncWrapper };
