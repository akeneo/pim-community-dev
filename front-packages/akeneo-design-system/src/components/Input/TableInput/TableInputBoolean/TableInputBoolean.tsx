import React from "react";
import { Badge } from "../../../Badge/Badge";
import { SelectInput, SelectInputProps } from "../../SelectInput/SelectInput";
import styled, { css } from "styled-components";
import { AkeneoThemedProps, getColor } from "../../../../theme";
import {Override} from '../../../../shared';
import {Dropdown} from "../../../Dropdown/Dropdown";
import {ArrowDownIcon, CloseIcon} from "../../../../icons";
import {useBooleanState} from "../../../../hooks";
import {IconButton} from "../../../IconButton/IconButton";

const BooleanButtonDropdown = styled(Dropdown)`
  width: 100%;
  cursor: pointer;
`;

const BooleanButton = styled.button<{highlighted: boolean} & AkeneoThemedProps>`
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
  
  ${({highlighted}) =>
  highlighted
    ? css`
          background: ${getColor('green', 10)};
          box-shadow: 0 0 0 1px ${getColor('green', 80)};
        `
    : css`
          background: none;
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
  {}, {
  value: boolean | null;
  onChange: (value: boolean | null) => void;
  yesLabel: string;
  noLabel: string;
  highlighted?: boolean;
  removeValueLabel: string;
  openDropdownLabel: string;
}>

const TableInputBoolean: React.FC<TableInputBooleanProps> = ({
  value,
  onChange,
  yesLabel,
  noLabel,
  highlighted = false,
  removeValueLabel,
  openDropdownLabel,
  ...rest
}) => {
  const [isOpen, open, close] = useBooleanState(false);

  const handleChange = (value: null | boolean) => {
    onChange(value);
    close();
  }

  return (
    <BooleanButtonDropdown>
      <BooleanButton tabIndex={-1} highlighted={highlighted} onClick={open}>
        {value !== null && (
          value ? <Badge level='primary'>{yesLabel}</Badge> : <Badge level='tertiary'>{noLabel}</Badge>
        )}
        &nbsp;
      </BooleanButton>
      <IconsPart>
        {value !== null && !isOpen &&
        <IconButton icon={<CloseIcon/>} size="small" title={removeValueLabel} ghost="borderless" level="tertiary"
                    onClick={() => handleChange(null)}/>
        }
        <IconButton icon={<ArrowDownIcon/>} size="small" title={openDropdownLabel} ghost="borderless" level="tertiary" onClick={open}/>
      </IconsPart>
      {isOpen && (
        <Dropdown.Overlay onClose={close} dropdownOpenerVisible={true}>
          <Dropdown.ItemCollection>
            <Dropdown.Item onClick={() => handleChange(true)}><Badge level='primary'>{yesLabel}</Badge></Dropdown.Item>
            <Dropdown.Item onClick={() => handleChange(false)}><Badge level='tertiary'>{noLabel}</Badge></Dropdown.Item>
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </BooleanButtonDropdown>
  );
};

export {TableInputBoolean};
