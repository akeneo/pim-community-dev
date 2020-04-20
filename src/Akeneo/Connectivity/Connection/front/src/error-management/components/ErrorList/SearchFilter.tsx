import React, {FC} from 'react';
import styled from '../../../common/styled-with-theme';
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
        <Container>
            <SearchInput
                value={value}
                onSearch={onSearch}
                placeholder={translate(
                    'akeneo_connectivity.connection.error_management.connection_monitoring.search_filter.placeholder'
                )}
            />
            <SearchCount>
                <Translate
                    id='akeneo_connectivity.connection.error_management.connection_monitoring.search_filter.result_count'
                    placeholders={{count: resultCount.toString()}}
                    count={resultCount}
                />
            </SearchCount>
        </Container>
    );
};

const Container = styled.div`
    border-bottom: 1px solid ${({theme}) => theme.color.grey100};
    display: flex;
    margin-bottom: 20px;
`;

const SearchCount = styled.span`
    color: #9452ba;
    line-height: 44px;
`;

export {SearchFilter};
