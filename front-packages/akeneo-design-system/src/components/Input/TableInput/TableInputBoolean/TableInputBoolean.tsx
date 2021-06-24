import React from "react";
import { Badge } from "../../../Badge/Badge";
import { SelectInput } from "../../SelectInput/SelectInput";
import styled, { css } from "styled-components";
import { AkeneoThemedProps, getColor } from "../../../../theme";

const BooleanSelectInput = styled(SelectInput)<{highlighted: boolean} & AkeneoThemedProps>`
  ${({highlighted}) => highlighted && css`
    & > div:first-child > div:first-child { background: ${getColor('green', 10)}; }
  `}
  input {
    height: 39px;
    padding-left: 10px;
    padding-right: 10px;
    border-radius: 0;
    border: none;

    ${({highlighted}) => highlighted ? css`
      box-shadow: 0 0 0 1px ${getColor('green', 80)};
    ` : css`
      background: none;
    `};

    &:focus {
      box-shadow: 0 0 0 1px ${getColor('grey', 100)};
    }
  }
`;

type TableInputBooleanProps = {
  value: boolean | null;
  onChange: (value: boolean) => void;
  yesLabel: string;
  noLabel: string;
  highlighted: boolean;
}

const TableInputBoolean = React.forwardRef<HTMLDivElement, TableInputBooleanProps>(
  ({
     value,
     onChange,
     yesLabel,
     noLabel,
     highlighted = false,
     ...rest
   }, forwardedRef: React.Ref<HTMLDivElement>) => {
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
        highlighted={highlighted}
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
