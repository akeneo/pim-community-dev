import React, {FC, useEffect, useState} from 'react';

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
        <input
            type='text'
            value={searchValue}
            onChange={event => setSearchValue(event.target.value)}
            placeholder={placeholder}
        />
    );
};

export {SearchInput};
