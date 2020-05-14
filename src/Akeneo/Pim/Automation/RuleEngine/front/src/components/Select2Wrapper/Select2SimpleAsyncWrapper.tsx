import React from "react";
import { Select2Ajax, Select2GlobalProps, Select2Wrapper } from "./Select2Wrapper";

type Props = Select2GlobalProps & {
  onChange?: (value: string) => void;
  value?: string;
  ajax: Select2Ajax;
}

const Select2SimpleAsyncWrapper: React.FC<Props> = (props) => {
  return <Select2Wrapper
    {...props}
    multiple={false}
  />
};

export { Select2SimpleAsyncWrapper }
