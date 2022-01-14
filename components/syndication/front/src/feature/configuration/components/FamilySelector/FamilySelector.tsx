import {getColor, Search, SubNavigationItem} from 'akeneo-design-system';
import React, {useState, useMemo} from 'react';
import styled from 'styled-components';
import {AddFamily} from './AddFamily';
import {DeleteFamily} from './DeleteFamily';

const SearchField = styled(Search)`
  border-radius: 30px;
  border-bottom: 0;
  border: 2px solid transparent;

  &:focus-within {
    border: 2px solid ${getColor('blue80')};
  }

  padding: 0 11px;
  flex: 1;
`;

const ScrollableNavigation = styled.div`
  overflow: auto;
  flex: 1;
`;

const ToolContainer = styled.div`
  display: flex;
  margin: 0 -10px;
  gap: 10px;
  align-items: center;
  justify-content: space-between;
`;

const FamilyItem = styled(SubNavigationItem)`
  text-transform: capitalize;

  & > div {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex: 1;

    & > button {
      display: none;
      position: relative;
    }
  }

  &:hover > div > button {
    display: block;
  }
`;

type FamilySelectorProps = {
  activeFamilyCodes: string[];
  families: {code: string; label: string}[];
  currentFamily: string | null;
  onCurrentFamilyChange: (newFamily: string) => void;
  onFamilyDelete: (familyToDelete: string) => void;
  onFamilyAdd: (familyToAdd: string) => void;
};

const FamilySelector = ({
  activeFamilyCodes,
  families,
  currentFamily,
  onCurrentFamilyChange,
  onFamilyDelete,
  onFamilyAdd,
}: FamilySelectorProps) => {
  const [searchValue, setSearchValue] = useState<string>('');
  const filteredFamiles = useMemo(() => {
    return families.filter(family => {
      return family.code.toLowerCase().includes(searchValue.toLowerCase()) && activeFamilyCodes.includes(family.code);
    });
  }, [searchValue, families, activeFamilyCodes]);

  const familiesToAdd = useMemo(
    () => families.filter(family => !activeFamilyCodes.includes(family.code)),
    [families, activeFamilyCodes]
  );

  return (
    <>
      <ToolContainer>
        <SearchField onSearchChange={setSearchValue} placeholder="Search" searchValue={searchValue} title="Search" />
        <AddFamily families={familiesToAdd} onFamilyAdd={newFamilyCode => onFamilyAdd(newFamilyCode)} />
      </ToolContainer>
      <ScrollableNavigation>
        {filteredFamiles
          .sort((first, second) => first.code.localeCompare(second.code))
          .map(family => (
            <FamilyItem
              key={family.code}
              active={family.code === currentFamily}
              onClick={() => onCurrentFamilyChange(family.code)}
            >
              <>{family.label ?? family.code}</>
              <DeleteFamily family={family} onFamilyDelete={onFamilyDelete} />
            </FamilyItem>
          ))}
      </ScrollableNavigation>
    </>
  );
};

export {FamilySelector};
