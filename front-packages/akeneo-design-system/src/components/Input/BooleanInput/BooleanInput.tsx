import React, {ReactNode, Ref, useCallback} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, CommonStyle, getColor} from '../../../theme';
import {DangerIcon} from '../../../icons/DangerIcon';
import {EraseIcon} from '../../../icons/EraseIcon';
import {LockIcon} from '../../../icons/LockIcon';
import {InputProps} from '../common';
import {Override} from '../../../shared';

const BooleanInputContainer = styled.div``;

const BooleanButton = styled.button<
  {
    value?: boolean;
    readOnly: boolean;
    invalid: boolean;
    size: 'normal' | 'small';
  } & AkeneoThemedProps
>`
  ${CommonStyle}
  height: ${({size}) => ('small' === size ? 30 : 40)}px;
  width: ${({size}) => ('small' === size ? 48 : 60)}px;
  display: inline-block;
  line-height: ${({size}) => ('small' === size ? 26 : 36)}px;
  text-align: center;
  vertical-align: middle;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
  background: ${getColor('white')};

  ${({readOnly, invalid}) =>
    readOnly
      ? css`
          border: 1px solid ${getColor('grey', 60)};
          color: ${getColor('grey', 80)};
          &:hover {
            background: ${getColor('white')};
            color: ${getColor('grey', 80)};
          }
        `
      : css`
          border: 1px solid ${invalid ? getColor('red', 100) : getColor('grey', 80)};
          cursor: pointer;
          &:hover {
            background: ${getColor('grey', 20)};
            color: ${getColor('grey', 140)};
          }
        `}
`;

const NoButton = styled(BooleanButton)`
  border-radius: 2px 0 0 2px;
  border-right-width: 1px;

  ${({value, readOnly, invalid}) =>
    value === false &&
    css`
      background: ${getColor('grey', readOnly ? 80 : 100)};
      border-color: ${invalid ? getColor('red', 100) : getColor('grey', readOnly ? 80 : 100)};
      color: ${getColor('white')};
      &:hover {
        background: ${getColor('grey', readOnly ? 80 : 120)};
        color: ${getColor('white')};
      }
      &:active {
        background: ${getColor('grey', readOnly ? 80 : 140)};
      }
    `}
`;

const YesButton = styled(BooleanButton)`
  border-radius: 0 2px 2px 0;
  border-left-width: 0;

  ${({value, readOnly, invalid}) =>
    value === true &&
    css`
      background: ${getColor('green', readOnly ? 60 : 100)};
      border-color: ${invalid ? getColor('red', 100) : getColor('grey', readOnly ? 60 : 100)};
      color: ${getColor('white')};

      &:hover {
        background: ${getColor('green', readOnly ? 60 : 120)};
        color: ${getColor('white')};
      }

      &:active {
        background: ${getColor('green', readOnly ? 60 : 140)};
      }
    `}
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
  color: 1px solid ${getColor('grey', 100)};
  vertical-align: middle;
  margin-left: 10px;
`;
const BooleanInputLockIcon = styled(LockIcon)``;

const ContainerInvalid = styled.div<AkeneoThemedProps>`
  display: flex;
  font-weight: 400;
  padding-right: 20px;
  color: ${getColor('red', 100)};
`;
const IconInvalidContainer = styled.span<AkeneoThemedProps>`
  margin: 2px 0;
  color: ${getColor('red', 100)};
`;
const TextInvalidContainer = styled.div<AkeneoThemedProps>`
  font-size: 11px;
  padding-left: 4px;
  white-space: break-spaces;
  flex: 1;

  a {
    color: ${getColor('red', 100)};
  }
`;

type BooleanInputProps = Override<
  InputProps<boolean>,
  (
    | {
        clearable?: true;
        value: boolean | null;
        onChange?: (value: boolean | null) => void;
        clearLabel: string;
      }
    | {
        clearable?: false;
        value: boolean;
        onChange?: (value: boolean) => void;
        clearLabel?: string;
      }
  ) & {
    readOnly: boolean;
    yesLabel: string;
    noLabel: string;
    invalid?: boolean;
    children?: ReactNode;
    size?: 'normal' | 'small';
  }
>;

/**
 * The BooleanInput is used to quickly switch between two possible states.
 */
const BooleanInput: React.FC<BooleanInputProps & {ref?: React.Ref<HTMLDivElement>}> = React.forwardRef<HTMLDivElement, BooleanInputProps>(
  (
    {
      value,
      readOnly,
      onChange,
      clearable = false,
      yesLabel,
      noLabel,
      clearLabel,
      invalid,
      children,
      size = 'normal',
      ...rest
    }: BooleanInputProps,
    forwardedRef: Ref<HTMLDivElement>
  ) => {
    const handleChange = useCallback(
      (value: any) => {
        if (!onChange) {
          return;
        }
        onChange(value);
      },
      [onChange, readOnly]
    );

    return (
      <BooleanInputContainer
        role="switch"
        aria-checked={null === value ? undefined : value}
        ref={forwardedRef}
        {...rest}
      >
        <NoButton
          value={value}
          readOnly={readOnly}
          aria-readonly={readOnly}
          disabled={readOnly}
          onClick={() => {
            handleChange(false);
          }}
          title={noLabel}
          aria-invalid={invalid}
          invalid={invalid}
          size={size}
        >
          {noLabel}
        </NoButton>

        <YesButton
          value={value}
          readOnly={readOnly}
          aria-readonly={readOnly}
          disabled={readOnly}
          onClick={() => {
            handleChange(true);
          }}
          title={yesLabel}
          aria-invalid={invalid}
          invalid={invalid}
          size={size}
        >
          {yesLabel}
        </YesButton>

        {value !== null && !readOnly && clearable && (
          <ClearButton
            onClick={() => {
              handleChange(null);
            }}
          >
            <BooleanInputEraseIcon size={16} />
            {clearLabel}
          </ClearButton>
        )}

        {readOnly && (
          <IconContainer>
            <BooleanInputLockIcon size={16} />
          </IconContainer>
        )}
        {invalid && children && (
          <ContainerInvalid>
            <IconInvalidContainer>{React.cloneElement(<DangerIcon size={13} />)}</IconInvalidContainer>
            <TextInvalidContainer>{children}</TextInvalidContainer>
          </ContainerInvalid>
        )}
      </BooleanInputContainer>
    );
  }
);

export {BooleanInput};
