import React, { Ref } from 'react';
import styled, { css } from 'styled-components';
import { AkeneoThemedProps, getColor, getFontSize } from "../../../theme";
import { EraseIcon, LockIcon } from "../../../icons";

const BooleanInputContainer = styled.div``;

const BooleanButton = styled.button<{
  value?: boolean,
  readOnly: boolean,
} & AkeneoThemedProps>`
  height: 40px;
  width: 60px;
  display: inline-block;
  line-height: 36px;
  font-family: 'Lato';
  text-align: center;
  vertical-align: middle;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
  background: ${getColor('white')};
  font-size: ${getFontSize('default')};
  
  ${({readOnly}) => readOnly ? css`
    border: 1px solid ${getColor('grey',60)}}
    color: ${getColor('grey', 80)}}
  ` : css`
    border: 1px solid ${getColor('grey',80)}}
    color: ${getColor('grey', 120)}}
    cursor: pointer;
  `};
`;

const NoButton = styled(BooleanButton)`
  border-radius: 2px 0 0 2px;
  
  ${({value, readOnly}) => value === false ? css`
    background: ${getColor('grey', readOnly ? 60 : 100)};
    border-color: ${getColor('grey', readOnly ? 60 : 100)};
    color: ${getColor('white')};
  ` : css`
    border-right-width: 0;
  `}
`

const YesButton = styled(BooleanButton)`
  border-radius: 0 2px 2px 0;
  
  ${({value, readOnly}) => {
    switch (value) {
      case true:
        return css`
          background: ${getColor('green', readOnly ? 60 : 100)};
          border-color: ${getColor('green', readOnly ? 60 : 100)};
          color: ${getColor('white')};
        `;
      case null:
        return css`
          border-left-width: ${({value}) => value !== false ? '' : '0'};
        `
    }
  }
}
`

const ClearButton = styled.button`
  font-family: 'Lato';
  border: 0;
  margin-left: 5px;
  padding: 5px;
  vertical-align: middle;
  font-size: ${getFontSize('default')};
  background: ${getColor('white')};
  color: ${getColor('grey', 100)};
  cursor: ${({readOnly}) => readOnly ? 'inherit' : 'pointer'};
`

const BooleanInputEraseIcon = styled(EraseIcon)`
  vertical-align: bottom;
  margin-right: 6px;
`

const BooleanInputLockIcon = styled(LockIcon)`
  vertical-align: middle;
  margin-left: 10px;
`

type BooleanInputProps = {
  clearable?: true;
  value: boolean | null;
  onChange?: (value: boolean | null) => void;
} | {
  clearable?: false;
  value: boolean;
  onChange?: (value: boolean) => void;
} & {
  readOnly?: boolean;
  yesLabel?: string;
  noLabel?: string;
  clearLabel?: string;
};


/**
 * TODO @stephane
 */
const BooleanInput = React.forwardRef<HTMLDivElement, BooleanInputProps>(
  ({
    value = null,
    readOnly = false,
    onChange,
    clearable = false,
    yesLabel = 'Yes',
    noLabel = 'No',
    clearLabel = 'Clear value',
     ...rest
  }: BooleanInputProps, forwardedRef: Ref<HTMLDivElement>) => {
    const handleChange = (value: boolean | null) => {
      if (!onChange || readOnly) {
        return;
      }
      onChange(value as boolean);
    }

    return (
      <BooleanInputContainer
        ref={forwardedRef}
        {...rest}
      >
        <NoButton
          value={value}
          readOnly={readOnly}
          disabled={readOnly}
          onClick={handleChange(false)}
          title={noLabel}
        >{noLabel}</NoButton>
        <YesButton
          value={value}
          readOnly={readOnly}
          disabled={readOnly}
          onClick={handleChange(true)}
          title={yesLabel}
        >{yesLabel}</YesButton>
        {value !== null && !readOnly && clearable &&
          <ClearButton
            onClick={handleChange(null)}
          ><BooleanInputEraseIcon size={16}/>{clearLabel}</ClearButton>
        }
        {readOnly &&
          <BooleanInputLockIcon size={16} color="#a1a9b7"/>
          /* I can't success to put a color in the LockIcon ; it's a grey100 :( */
        }
      </BooleanInputContainer>
    );
  }
);

export {BooleanInput};
