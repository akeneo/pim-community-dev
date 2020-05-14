import React from "react";
import { Select2GlobalProps, Select2Option, Select2Wrapper } from "./Select2Wrapper";

type Props = Select2GlobalProps & {
  data: Select2Option[];
  onChange?: (value: string) => void;
  value?: string;
}

const Select2SimpleSyncWrapper: React.FC<Props> = (props) => {
  return <Select2Wrapper
    {...props}
    multiple={false}
  />
};

export { Select2SimpleSyncWrapper }
