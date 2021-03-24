import React, {FC} from 'react';
import styled from '../../common/styled-with-theme';

type Props = {
    value: string;
    onSearch: (value: string) => void;
    placeholder: string;
};

const SearchInput: FC<Props> = ({value, onSearch, placeholder}) => {
    return (
        <Input
            type='text'
            value={value}
            onChange={event => onSearch(event.target.value)}
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

export default SearchInput;
