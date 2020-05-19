import React from 'react';
import {
  Select2GlobalProps, Select2Option,
  Select2Value,
  Select2Wrapper,
} from './Select2Wrapper';

type Props = Select2GlobalProps & {
  onValueChange?: (value: Select2Value[]) => void;
  value?: Select2Value[];
  data: Select2Option[];
};

const Select2MultiSyncWrapper: React.FC<Props> = props => {
  return <Select2Wrapper {...props} multiple={true} />;
};

export { Select2MultiSyncWrapper };
