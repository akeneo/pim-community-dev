import React, {useEffect, useState} from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import styled from 'styled-components';
import useDebounce from 'akeneoassetmanager/platform/hook/use-debounce';
import {useFocus} from 'akeneoassetmanager/application/hooks/input';
import {SearchIcon, getColor, getFontSize} from 'akeneo-design-system';

type SearchFieldProps = {
  value: string;
  onChange: (newValue: string) => void;
};

const Container = styled.div`
  display: flex;
  flex: 1;
`;

const SearchLogo = styled(SearchIcon)`
  margin-right: 6px;
`;

const SearchInput = styled.input`
  flex: 1;
  outline: none;
  border: none;
  color: ${getColor('grey', 120)};
  font-size: ${getFontSize('default')};
`;

const SearchField = ({value, onChange}: SearchFieldProps) => {
  const [userSearch, setUserSearch] = useState(value);
  const debouncedUserSearch = useDebounce(userSearch, 250);
  const [inputRef] = useFocus();

  useEffect(() => {
    onChange(debouncedUserSearch);
  }, [debouncedUserSearch]);

  const onInputChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setUserSearch(event.target.value);
  };

  return (
    <Container>
      <SearchLogo />
      <SearchInput
        type="text"
        autoComplete="off"
        placeholder={__('pim_asset_manager.asset.grid.search')}
        defaultValue={userSearch}
        onChange={onInputChange}
        ref={inputRef}
      />
    </Container>
  );
};

export default SearchField;
