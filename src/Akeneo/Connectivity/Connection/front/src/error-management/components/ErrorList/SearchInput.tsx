import React, {FC, useEffect, useState} from 'react';
import styled from '../../../common/styled-with-theme';

const DEBOUNCE_TIME = 300;

type Props = {
    value: string;
    onSearch: (value: string) => void;
    placeholder: string;
};

const SearchInput: FC<Props> = ({value, onSearch, placeholder}) => {
    const [searchValue, setSearchValue] = useState<string>(value);

    useEffect(() => {
        if (value === searchValue) {
            return;
        }

        const timeoutId = setTimeout(() => onSearch(searchValue), DEBOUNCE_TIME);

        return () => clearTimeout(timeoutId);
    }, [value, searchValue, onSearch]);

    return (
        <Input
            type='text'
            value={searchValue}
            onChange={event => setSearchValue(event.target.value)}
            placeholder={placeholder}
        />
    );
};

const Input = styled.input`
    height: 44px;
    line-height: 44px;
    color: #67768a;
    font-size: 13px;
    border: none;
    background: url(${({theme}) => theme.icon.search}) no-repeat 0 center;
    padding-left: 30px;
    flex-grow: 1;
    margin-top: 20px;
    width: 100%;
`;

export {SearchInput};
