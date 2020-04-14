import React, {FC} from 'react';
import {Translate, useTranslate} from '../../../shared/translate';
import {SearchInput} from './SearchInput';

type Props = {
    value: string;
    onSearch: (value: string) => void;
    resultCount: number;
};

const SearchFilter: FC<Props> = ({value, onSearch, resultCount}) => {
    const translate = useTranslate();

    return (
        <>
            <SearchInput
                value={value}
                onSearch={onSearch}
                placeholder={translate(
                    'akeneo_connectivity.connection.error_management.connection_monitoring.search_filter.placeholder'
                )}
            />
            <Translate
                id='akeneo_connectivity.connection.error_management.connection_monitoring.search_filter.result_count'
                placeholders={{count: resultCount.toString()}}
            />
        </>
    );
};

export {SearchFilter};
