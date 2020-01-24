import * as React from 'react';
import {useEffect, useState} from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import styled from 'styled-components';
import SearchIcon from 'akeneoassetmanager/application/component/app/icon/search';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import useDebounce from 'akeneoassetmanager/platform/hook/use-debounce';
import {useFocus} from 'akeneoassetmanager/application/hooks/input';

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
  color: ${(props: ThemedProps<void>) => props.theme.color.grey120};
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.default};
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
