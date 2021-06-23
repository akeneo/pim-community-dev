import React from "react";
import { Badge } from "../../../Badge/Badge";
import { SelectInput } from "../../SelectInput/SelectInput";
import styled from "styled-components";

const BooleanSelectInput = styled(SelectInput)`
  input {
    border-width: 0;
  }
`;

type TableInputBooleanProps = {
  value: boolean | null;
  onChange: (value: boolean) => void;
  yesLabel: string;
  noLabel: string;
}

const TableInputBoolean = React.forwardRef<HTMLDivElement, TableInputBooleanProps>(
  ({value, onChange, yesLabel, noLabel, ...rest}, forwardedRef: React.Ref<HTMLDivElement>) => {
    const label = typeof value === 'undefined' ? '' : value ? yesLabel : noLabel;
    const handleChange = (value) => {
      if (value === null) {
        onChange(null);
      } else {
        onChange(value === 'true');
      }
    }
    return (
      <BooleanSelectInput
        onChange={handleChange}
        value={value === null ? null : value ? 'true' : 'false'}
        {...rest}
        ref={forwardedRef}
      >
        <SelectInput.Option title={yesLabel} value="true"><Badge level='primary'>{yesLabel}</Badge></SelectInput.Option>
        <SelectInput.Option title={noLabel} value="false"><Badge level='tertiary'>{noLabel}</Badge></SelectInput.Option>
      </BooleanSelectInput>
    );
  }
);

export {TableInputBoolean};
