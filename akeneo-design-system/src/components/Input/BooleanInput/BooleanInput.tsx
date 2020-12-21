import React, {Ref, useCallback} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, CommonStyle, getColor} from '../../../theme';
import {EraseIcon, LockIcon} from '../../../icons';
import {InputProps} from '../InputProps';
import {Override} from '../../../shared';
import {useSkeleton} from '../../../hooks';
import {applySkeletonStyle, SkeletonProps} from '../../Skeleton/Skeleton';

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

  ${applySkeletonStyle()}
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
      case false:
        return css`
          border-left-width: 0;
        `;
      default:
        return '';
    }
  }}

  ${applySkeletonStyle()}
`;

const ClearButton = styled.button<AkeneoThemedProps & SkeletonProps>`
  ${CommonStyle}
  border: 0;
  margin-left: 5px;
  padding: 5px;
  vertical-align: middle;
  background: ${getColor('white')};
  color: ${getColor('grey', 100)};
  ${({readOnly}) => !readOnly && 'cursor: pointer'};
  ${applySkeletonStyle()}
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

type BooleanInputProps = Override<
  InputProps,
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
  }
>;

/**
 * The BooleanInput is used to quickly switch between two possible states.
 */
const BooleanInput = React.forwardRef<HTMLDivElement, BooleanInputProps>(
  (
    {value, readOnly, onChange, clearable = false, yesLabel, noLabel, clearLabel, ...rest}: BooleanInputProps,
    forwardedRef: Ref<HTMLDivElement>
  ) => {
    const handleChange = useCallback(
      value => {
        if (!onChange) {
          return;
        }
        onChange(value);
      },
      [onChange, readOnly]
    );

    const skeleton = useSkeleton();

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
          skeleton={skeleton}
          onClick={() => {
            handleChange(false);
          }}
          title={noLabel}
        >
          {noLabel}
        </NoButton>

        <YesButton
          value={value}
          readOnly={readOnly}
          aria-readonly={readOnly}
          disabled={readOnly}
          skeleton={skeleton}
          onClick={() => {
            handleChange(true);
          }}
          title={yesLabel}
        >
          {yesLabel}
        </YesButton>

        {value !== null && !readOnly && clearable && (
          <ClearButton
            onClick={() => {
              handleChange(null);
            }}
            skeleton={skeleton}
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
      </BooleanInputContainer>
    );
  }
);

export {BooleanInput};
