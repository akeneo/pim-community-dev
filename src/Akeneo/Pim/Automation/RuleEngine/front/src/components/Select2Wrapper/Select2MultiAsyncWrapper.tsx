import React from "react";
import { Select2Ajax, Select2GlobalProps, Select2Value, Select2Wrapper } from "./Select2Wrapper";

type Props = Select2GlobalProps & {
  onChange?: (value: Select2Value[]) => void;
  value?: Select2Value[];
  ajax: Select2Ajax;
}

const Select2MultiAsyncWrapper: React.FC<Props> = (props) => {
  return <Select2Wrapper
    {...props}
    multiple={true}
  />
};

export { Select2MultiAsyncWrapper }
