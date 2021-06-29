import React from 'react';
import {Dropdown} from "../../../Dropdown/Dropdown";
import {useBooleanState, useDebounce, usePaginatedResults} from "../../../../hooks";
import {ArrowDownIcon, CloseIcon} from "../../../../icons";
import {Search} from "../../../Search/Search";
import styled from "styled-components";
import {IconButton} from "../../../IconButton/IconButton";
import {CommonStyle, getColor} from "../../../../theme";

const SelectButtonDropdown = styled(Dropdown)`
  width: 100%;
  cursor: pointer;
`;

const SelectButton = styled.button`
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
`;

const IconsPart = styled.div`
  display: inline-flex;
  gap: 10px;
  position: absolute;
  right: 10px;
  height: 39px;
  align-items: center;
`;

type PageResult = {
  id: string | number;
  text: string;
}

type TableInputSelectProps = {
  value: string | number | null;
  onChange: (value: string | number | null) => void;
  searchLabel: string;
  highlighted: boolean;
  removeValueLabel: string;
  openDropdownLabel: string;
  fetchNextPage: (page: number, searchValue: string) => PageResult[];
}

const TableInputSelect: React.FC<TableInputSelectProps> = ({
  value,
  onChange,
  searchLabel,
  fetchNextPage,
  removeValueLabel,
  openDropdownLabel,
  highlighted = false,
  ...rest
}) => {
    const [isOpen, open, close] = useBooleanState(false);
    const [searchValue, setSearchValue] = React.useState('');
    const debouncedSearchValue = useDebounce(searchValue);
    const [items, handleNextPage] = usePaginatedResults<PageResult>(
        page => Promise.resolve(fetchNextPage(page, debouncedSearchValue)),
        [debouncedSearchValue],
        isOpen
    );
    const removeValue = () => {
      onChange(null);
    };

    return <SelectButtonDropdown {...rest}>
        <SelectButton onClick={open} tabIndex={-1}>
          {value}
        </SelectButton>
        <IconsPart>
          {value !== null && !isOpen &&
          <IconButton icon={<CloseIcon/>} size="small" title={removeValueLabel} ghost="borderless" level="tertiary"
                      onClick={removeValue}/>
          }
          <IconButton icon={<ArrowDownIcon/>} size="small" title={openDropdownLabel} ghost="borderless" level="tertiary" onClick={open}/>
        </IconsPart>
        {isOpen && (
            <Dropdown.Overlay verticalPosition="down" onClose={close}>
                <Dropdown.Header>
                    <Search
                        onSearchChange={setSearchValue}
                        placeholder="Search"
                        searchValue={searchValue}
                        title="Search"
                    />
                </Dropdown.Header>
                <Dropdown.ItemCollection onNextPage={handleNextPage}>
                    {items.map((item, index) => (
                        <Dropdown.Item
                          key={index}
                          onClick={() => {
                            onChange(item.id);
                            setSearchValue('');
                            close();
                          }}
                        >{item.text}</Dropdown.Item>
                    ))}
                </Dropdown.ItemCollection>
            </Dropdown.Overlay>
        )}
    </SelectButtonDropdown>
};

export { TableInputSelect };
