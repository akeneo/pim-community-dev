import React from 'react';
import {
  Select2GlobalProps,
  Select2Option,
  Select2Value,
  Select2Wrapper,
} from './Select2Wrapper';

type Props = Select2GlobalProps & {
  data: Select2Option[];
  onValueChange?: (value: Select2Value) => void;
  value?: Select2Value;
};

const Select2SimpleSyncWrapper: React.FC<Props> = props => {
  return <Select2Wrapper {...props} multiple={false} />;
};

export { Select2SimpleSyncWrapper };
