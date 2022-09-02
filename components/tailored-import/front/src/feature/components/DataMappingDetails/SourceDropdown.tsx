import React, {useState, useRef, useEffect} from 'react';
import {
  BlockButton,
  Dropdown,
  Search,
  GroupsIllustration,
  useBooleanState,
  useDebounce,
  useAutoFocus,
  ArrowDownIcon,
} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Column, generateColumnName} from '../../models';

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
      <BlockButton
        onClick={open}
        icon={<ArrowDownIcon title="akeneo.tailored_import.data_mapping.sources.add.title" />}
      >
        {translate('akeneo.tailored_import.data_mapping.sources.add.label')}
      </BlockButton>
      {isOpen && (
        <Dropdown.Overlay verticalPosition="down" onClose={close} fullWidth={true}>
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
                {generateColumnName(column.index, column.label)}
              </Dropdown.Item>
            ))}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

export {SourceDropdown};
