import React from 'react';
import {
  Select2GlobalProps,
  Select2Option,
  Select2Value,
  Select2Wrapper,
} from './Select2Wrapper';

type Props = Select2GlobalProps & {
  data: Select2Option[];
  onChange?: (value: Select2Value) => void;
  value?: Select2Value[];
};

const Select2MultiSyncWrapper: React.FC<Props> = props => {
  return <Select2Wrapper {...props} multiple={true} />;
};

export { Select2MultiSyncWrapper };
