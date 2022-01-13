import React, {useState, useRef, useEffect} from 'react';
import {Column, generateColumnName} from '../models';
import {
  ArrowDownIcon,
  Button,
  Dropdown,
  Search,
  GroupsIllustration,
  useBooleanState,
  useDebounce,
  useAutoFocus,
} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

type SourceDropdownProps = {
  columns: Column[];
  onColumnSelected: (selectedColumn: Column) => void;
};

const SourceDropdown = ({columns, onColumnSelected}: SourceDropdownProps) => {
  const translate = useTranslate();
  const [isOpen, open, close] = useBooleanState(false);
  const [searchValue, setSearchValue] = useState('');
  const debouncedSearchValue = useDebounce(searchValue);
  const inputRef = useRef<HTMLInputElement>(null);
  const focus = useAutoFocus(inputRef);

  useEffect(() => {
    isOpen && focus();
  }, [isOpen, focus]);

  const handleColumnSelected = (selectedColumn: Column) => {
    onColumnSelected(selectedColumn);
    close();
  };

  const filteredColumns = columns.filter(column =>
    column.label.toLocaleLowerCase().includes(debouncedSearchValue.toLowerCase())
  );

  return (
    <Dropdown>
      <Button onClick={open}>
        {translate('akeneo.tailored_import.data_mapping.source.add')} <ArrowDownIcon />
      </Button>
      {isOpen && (
        <Dropdown.Overlay verticalPosition="down" onClose={close}>
          <Dropdown.Header>
            <Search
              onSearchChange={setSearchValue}
              placeholder={translate('pim_common.search')}
              searchValue={searchValue}
              title={translate('pim_common.search')}
              inputRef={inputRef}
            />
          </Dropdown.Header>
          <Dropdown.ItemCollection
            noResultIllustration={<GroupsIllustration />}
            noResultTitle={translate('pim_common.no_result')}
          >
            {filteredColumns.map(column => (
              <Dropdown.Item key={column.uuid} onClick={() => handleColumnSelected(column)}>
                {generateColumnName(column)}
              </Dropdown.Item>
            ))}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

export {SourceDropdown};
