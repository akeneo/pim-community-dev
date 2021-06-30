import React, {ReactElement} from 'react';
import {Dropdown} from "../../../Dropdown/Dropdown";
import {useBooleanState, useDebounce, usePaginatedResults} from "../../../../hooks";
import {ArrowDownIcon, CloseIcon} from "../../../../icons";
import {Search} from "../../../Search/Search";
import styled, {css} from "styled-components";
import {IconButton} from "../../../IconButton/IconButton";
import {AkeneoThemedProps, CommonStyle, getColor} from "../../../../theme";

const SelectButtonDropdown = styled(Dropdown)`
  width: 100%;
  cursor: pointer;
`;

const SelectButton = styled.button<{highlighted: boolean} & AkeneoThemedProps>`
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

type TableInputSelectProps = {
  value: ReactElement | null;
  onClear: () => void;
  highlighted: boolean;
  removeValueLabel: string;
  openDropdownLabel: string;
  onNextPage?: () => void;
  searchValue: string;
  onSearchChange: (search: string) => void;
  searchPlaceholder: string;
  searchTitle: string;
}

const TableInputSelect: React.FC<TableInputSelectProps> = ({
  value,
  onClear,
  fetchNextPage,
  removeValueLabel,
  openDropdownLabel,
  highlighted = false,
  searchValue,
  searchPlaceholder,
  onSearchChange,
  searchTitle,
  onNextPage,
  children,
  ...rest
}) => {
    const [isOpen, open, close] = useBooleanState(false);

    React.useEffect(() => {
      close();
      onSearchChange('');
    }, [value]);

    return <SelectButtonDropdown {...rest}>
        <SelectButton onClick={open} tabIndex={-1} highlighted={highlighted}>
          {value}&nbsp;
        </SelectButton>
        <IconsPart>
          {value && !isOpen &&
          <IconButton icon={<CloseIcon/>} size="small" title={removeValueLabel} ghost="borderless" level="tertiary"
                      onClick={onClear}/>
          }
          <IconButton icon={<ArrowDownIcon/>} size="small" title={openDropdownLabel} ghost="borderless" level="tertiary" onClick={open}/>
        </IconsPart>
        {isOpen && (
            <Dropdown.Overlay onClose={close} dropdownOpenerVisible={true}>
                <Dropdown.Header>
                    <Search
                        onSearchChange={onSearchChange}
                        placeholder={searchPlaceholder}
                        searchValue={searchValue}
                        title={searchTitle}
                    />
                </Dropdown.Header>
                <Dropdown.ItemCollection onNextPage={onNextPage}>
                  {children}
                </Dropdown.ItemCollection>
            </Dropdown.Overlay>
        )}
    </SelectButtonDropdown>
};

export { TableInputSelect };
