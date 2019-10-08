import * as React from 'react';
import __ from 'akeneoreferenceentity/tools/translator';
import styled from 'styled-components';
import SearchIcon from 'akeneoassetmanager/application/component/app/icon/search';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';

type SearchFieldProps = {
  value: string;
  onChange: (newValue: string) => void;
};

const Container = styled.div`
  display: flex;
  flex: 1;
  min-width: 300px;
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

let timer: any = null;
const SearchField = ({value, onChange}: SearchFieldProps) => {
  const inputRef = React.useRef<HTMLInputElement>(null);
  if (null !== inputRef.current) {
    inputRef.current.focus();
  }

  const onInputUpdated = (event: React.ChangeEvent<HTMLInputElement>) => {
    const userSearch = event.currentTarget.value;
    if (null !== timer) {
      clearTimeout(timer as any);
    }
    timer = setTimeout(() => {
      onChange(userSearch);
    }, 250) as any;
  };

  return (
    <Container>
      <SearchLogo />
      <SearchInput
        type="text"
        autoComplete="off"
        placeholder={__('pim_asset_manager.asset.grid.search')}
        defaultValue={value}
        onChange={onInputUpdated}
        ref={inputRef}
      />
    </Container>
  );
};

export default SearchField;
