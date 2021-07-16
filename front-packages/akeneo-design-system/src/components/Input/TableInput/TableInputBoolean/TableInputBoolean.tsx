import React from 'react';
import {Badge} from '../../../Badge/Badge';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../../../theme';
import {Override} from '../../../../shared';
import {Dropdown} from '../../../Dropdown/Dropdown';
import {ArrowDownIcon, CloseIcon, LockIcon} from '../../../../icons';
import {useBooleanState} from '../../../../hooks';
import {IconButton} from '../../../IconButton/IconButton';

const BooleanButtonDropdown = styled(Dropdown)<{readOnly: boolean} & AkeneoThemedProps>`
  width: 100%;
  color: ${({readOnly}) => (readOnly ? getColor('grey', 100) : getColor('grey', 140))};
`;

const BooleanButton = styled.button<{highlighted: boolean; inError: boolean; readOnly: boolean} & AkeneoThemedProps>`
  color: ${getColor('grey', 140)};
  width: 100%;
  background: none;
  border: none;
  text-align: left;
  display: flex;
  justify-content: space-between;
  padding: 0 10px;
  height: 39px;
  line-height: 39px;
  align-items: center;
  cursor: ${({readOnly}) => (readOnly ? 'not-allowed' : 'pointer')};
  background: none;

  ${({highlighted, inError}) =>
    highlighted &&
    !inError &&
    css`
      background: ${getColor('green', 10)};
      box-shadow: 0 0 0 1px ${getColor('green', 80)};
    `};

  ${({inError}) =>
    inError &&
    css`
      background: ${getColor('red', 10)};
      box-shadow: 0 0 0 1px ${getColor('red', 80)};
    `};
`;

const IconsPart = styled.div`
  display: inline-flex;
  gap: 10px;
  position: absolute;
  right: 10px;
  height: 39px;
  align-items: center;
`;

type TableInputBooleanProps = Override<
  React.HTMLAttributes<HTMLDivElement>,
  {
    value: boolean | null;
    onChange: (value: boolean | null) => void;
    yesLabel: string;
    noLabel: string;
    highlighted?: boolean;
    clearLabel: string;
    openDropdownLabel: string;
    inError?: boolean;
    readOnly?: boolean;
  }
>;

const TableInputBoolean: React.FC<TableInputBooleanProps> = ({
  value,
  onChange,
  yesLabel,
  noLabel,
  highlighted = false,
  clearLabel,
  openDropdownLabel,
  readOnly = false,
  inError = false,
  ...rest
}) => {
  const [isOpen, open, close] = useBooleanState(false);

  const handleChange = (value: null | boolean) => {
    onChange(value);
    close();
  };

  return (
    <BooleanButtonDropdown readOnly={readOnly} {...rest}>
      <BooleanButton
        readOnly={readOnly}
        tabIndex={-1}
        highlighted={highlighted}
        onClick={() => {
          if (!readOnly) open();
        }}
        inError={inError}
      >
        {value !== null &&
          (value ? <Badge level="primary">{yesLabel}</Badge> : <Badge level="tertiary">{noLabel}</Badge>)}
        &nbsp;
      </BooleanButton>
      <IconsPart>
        {value !== null && !readOnly && !isOpen && (
          <IconButton
            icon={<CloseIcon />}
            size="small"
            title={clearLabel}
            ghost="borderless"
            level="tertiary"
            onClick={() => handleChange(null)}
          />
        )}
        {!readOnly && (
          <IconButton
            icon={<ArrowDownIcon />}
            size="small"
            title={openDropdownLabel}
            ghost="borderless"
            level="tertiary"
            onClick={open}
          />
        )}
        {readOnly && <LockIcon size={16} />}
      </IconsPart>
      {isOpen && (
        <Dropdown.Overlay onClose={close} dropdownOpenerVisible={true}>
          <Dropdown.ItemCollection>
            <Dropdown.Item onClick={() => handleChange(true)}>
              <Badge level="primary">{yesLabel}</Badge>
            </Dropdown.Item>
            <Dropdown.Item onClick={() => handleChange(false)}>
              <Badge level="tertiary">{noLabel}</Badge>
            </Dropdown.Item>
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </BooleanButtonDropdown>
  );
};

export {TableInputBoolean};
