import React from 'react';
import {Badge} from '../../../Badge/Badge';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../../../theme';
import {Override} from '../../../../shared';
import {Dropdown} from '../../../Dropdown/Dropdown';
import {ArrowDownIcon} from '../../../../icons/ArrowDownIcon';
import {CloseIcon} from '../../../../icons/CloseIcon';
import {useBooleanState} from '../../../../hooks';
import {IconButton} from '../../../IconButton/IconButton';
import {TableInputReadOnlyCell} from '../shared/TableInputReadOnlyCell';
import {TableInputContext} from '../TableInputContext';
import {highlightCell} from '../shared/highlightCell';

const BooleanButtonDropdown = styled(Dropdown)`
  width: 100%;
  color: ${getColor('grey', 140)};
`;

const BooleanButton = styled.button<{highlighted: boolean; inError: boolean} & AkeneoThemedProps>`
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
  cursor: pointer;
  background: none;

  ${highlightCell};
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
  inError = false,
  ...rest
}) => {
  const [isOpen, open, close] = useBooleanState(false);

  const handleChange = (value: null | boolean) => {
    onChange(value);
    close();
  };

  const YesBadge = <Badge level="primary">{yesLabel}</Badge>;
  const NoBadge = <Badge level="tertiary">{noLabel}</Badge>;

  const {readOnly} = React.useContext(TableInputContext);

  if (readOnly) {
    return (
      <TableInputReadOnlyCell title={value !== null ? (value ? yesLabel : noLabel) : undefined}>
        {value !== null && (value ? YesBadge : NoBadge)}
      </TableInputReadOnlyCell>
    );
  }

  return (
    <BooleanButtonDropdown {...rest}>
      <BooleanButton
        tabIndex={-1}
        highlighted={highlighted}
        onClick={(e: MouseEvent) => {
          e.preventDefault();
          open();
        }}
        inError={inError}
      >
        {value !== null && (value ? YesBadge : NoBadge)}
        &nbsp;
      </BooleanButton>
      <IconsPart>
        {value !== null && !isOpen && (
          <IconButton
            icon={<CloseIcon />}
            size="small"
            title={clearLabel}
            ghost="borderless"
            level="tertiary"
            onClick={() => handleChange(null)}
          />
        )}
        <IconButton
          icon={<ArrowDownIcon />}
          size="small"
          title={openDropdownLabel}
          ghost="borderless"
          level="tertiary"
          onClick={open}
        />
      </IconsPart>
      {isOpen && (
        <Dropdown.Overlay onClose={close} dropdownOpenerVisible={true} horizontalPosition="left">
          <Dropdown.ItemCollection>
            <Dropdown.Item onClick={() => handleChange(true)}>{YesBadge}</Dropdown.Item>
            <Dropdown.Item onClick={() => handleChange(false)}>{NoBadge}</Dropdown.Item>
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </BooleanButtonDropdown>
  );
};

export {TableInputBoolean};
