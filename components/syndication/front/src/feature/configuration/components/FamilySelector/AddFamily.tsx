import {useBooleanState, Dropdown, IconButton, PlusIcon, Search, GroupsIllustration} from 'akeneo-design-system';
import React, {useCallback, useMemo, useState} from 'react';

type AddFamilyProps = {
  families: {code: string; label: string}[];
  onFamilyAdd: (familyCode: string) => void;
};

const AddFamily = ({families, onFamilyAdd}: AddFamilyProps) => {
  const [isOpen, open, close] = useBooleanState(false);
  const [searchValue, setSearchValue] = useState('');

  const filteredFamiles = useMemo(() => {
    return Object.values(families).filter(family => {
      return family.code.toLowerCase().includes(searchValue.toLowerCase());
    });
  }, [searchValue, families]);

  const handleClose = useCallback(() => {
    close();
    setSearchValue('');
  }, [close, setSearchValue]);

  return (
    <Dropdown>
      <IconButton ghost icon={<PlusIcon />} level="tertiary" onClick={open} size="default" title="Icon Button" />
      {isOpen && (
        <Dropdown.Overlay verticalPosition="down" horizontalPosition="left" onClose={handleClose}>
          <Dropdown.Header>
            <Search onSearchChange={setSearchValue} placeholder="Search" searchValue={searchValue} title="Search" />
          </Dropdown.Header>
          <Dropdown.ItemCollection
            noResultIllustration={<GroupsIllustration />}
            noResultTitle="Sorry, there is no results."
          >
            {filteredFamiles
              .sort((first, second) => first.code.localeCompare(second.code))
              .map(family => (
                <Dropdown.Item
                  key={family.code}
                  onClick={() => {
                    onFamilyAdd(family.code);
                    handleClose();
                  }}
                >
                  {family.label}
                </Dropdown.Item>
              ))}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

export {AddFamily};
