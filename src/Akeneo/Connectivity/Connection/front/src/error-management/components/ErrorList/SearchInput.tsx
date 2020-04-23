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
    background: url(${({theme}) => theme.icon.search}) no-repeat 0 center;
    border: none;
    flex-grow: 1;
    line-height: 44px;
    outline: none;
    padding: 0 0 0 30px;

    &::placeholder {
        color: ${({theme}) => theme.color.grey120};
    }
`;

export {SearchInput};
