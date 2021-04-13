import React, {useEffect, useRef, useState} from 'react';
import styled from 'styled-components';
import {SearchIcon, getColor, getFontSize, useAutoFocus} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import useDebounce from 'akeneoassetmanager/platform/hook/use-debounce';

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
  const translate = useTranslate();
  const [userSearch, setUserSearch] = useState(value);
  const debouncedUserSearch = useDebounce(userSearch, 250);
  const inputRef = useRef<HTMLInputElement>(null);

  useAutoFocus(inputRef);

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
        placeholder={translate('pim_asset_manager.asset.grid.search')}
        defaultValue={userSearch}
        onChange={onInputChange}
        ref={inputRef}
      />
    </Container>
  );
};

export default SearchField;
