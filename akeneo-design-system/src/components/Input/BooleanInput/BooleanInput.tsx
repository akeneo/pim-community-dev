import React, {Ref, useCallback} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, CommonStyle, getColor} from '../../../theme';
import {EraseIcon, LockIcon} from '../../../icons';
import {useTranslate} from '../../../hooks/useTranslate';

const BooleanInputContainer = styled.div``;

const BooleanButton = styled.button<
  {
    value?: boolean;
    readOnly: boolean;
  } & AkeneoThemedProps
>`
  ${CommonStyle}
  height: 40px;
  width: 60px;
  display: inline-block;
  line-height: 36px;
  text-align: center;
  vertical-align: middle;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
  background: ${getColor('white')};

  ${({readOnly}) =>
    readOnly
      ? css`
    border: 1px solid ${getColor('grey', 60)}}
    color: ${getColor('grey', 80)}}
  `
      : css`
    border: 1px solid ${getColor('grey', 80)}}
    cursor: pointer;
  `};
`;

const NoButton = styled(BooleanButton)`
  border-radius: 2px 0 0 2px;

  ${({value, readOnly}) =>
    value === false
      ? css`
          background: ${getColor('grey', readOnly ? 60 : 100)};
          border-color: ${getColor('grey', readOnly ? 60 : 100)};
          color: ${getColor('white')};
        `
      : css`
          border-right-width: 0;
        `}
`;

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
          border-left-width: 0;
        `;
      default:
        return '';
    }
  }}
`;

const ClearButton = styled.button`
  ${CommonStyle}
  border: 0;
  margin-left: 5px;
  padding: 5px;
  vertical-align: middle;
  background: ${getColor('white')};
  color: ${getColor('grey', 100)};
  ${({readOnly}) => !readOnly && 'cursor: pointer'};
`;

const BooleanInputEraseIcon = styled(EraseIcon)`
  vertical-align: bottom;
  margin-right: 6px;
`;

const IconContainer = styled.span`
  color: 1px solid ${getColor('grey', 100)}}
  vertical-align: middle;
  margin-left: 10px;
`;
const BooleanInputLockIcon = styled(LockIcon)``;

type BooleanInputProps = (
  | {
      clearable?: true;
      value: boolean | null;
      onChange?: (value: boolean | null) => void;
    }
  | {
      clearable?: false;
      value: boolean;
      onChange?: (value: boolean) => void;
    }
) & {
  readOnly?: boolean;
};

/**
 * Toggle is used to quickly switch between two possible states. They are commonly used for "yes/no" switches.
 * The boolean can in some cases have a 3rd state, EMPTY. In this case, a clear button allows the user to empty the
 * value of the field.
 */
const BooleanInput = React.forwardRef<HTMLDivElement, BooleanInputProps>(
  (
    {value, readOnly = false, onChange, clearable = false, ...rest}: BooleanInputProps,
    forwardedRef: Ref<HTMLDivElement>
  ) => {
    const translate = useTranslate();

    const handleChange = useCallback(
      (value: boolean | null) => {
        if (!onChange) {
          return;
        }
        onChange(value as boolean);
      },
      [onChange, readOnly]
    );

    return (
      <BooleanInputContainer ref={forwardedRef} {...rest}>
        <NoButton
          value={value}
          readOnly={readOnly}
          disabled={readOnly}
          onClick={() => {
            handleChange(false);
          }}
          title={translate('No')}
        >
          {translate('No')}
        </NoButton>

        <YesButton
          value={value}
          readOnly={readOnly}
          disabled={readOnly}
          onClick={() => {
            handleChange(true);
          }}
          title={translate('Yes')}
        >
          {translate('Yes')}
        </YesButton>

        {value !== null && !readOnly && clearable && (
          <ClearButton
            onClick={() => {
              handleChange(null);
            }}
          >
            <BooleanInputEraseIcon size={16} />
            {translate('Clear value')}
          </ClearButton>
        )}

        {readOnly && (
          <IconContainer>
            <BooleanInputLockIcon size={16} />
          </IconContainer>
        )}
      </BooleanInputContainer>
    );
  }
);

export {BooleanInput};
