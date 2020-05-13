import React, {FC, useState} from 'react';
import {Table, TableCell, TableHeaderCell, TableHeaderRow, TableRow} from '../../../common';
import styled from '../../../common/styled-with-theme';
import {useDateFormatter} from '../../../shared/formatter/use-date-formatter';
import {Translate} from '../../../shared/translate';
import {ConnectionError} from '../../hooks/api/use-connection-errors';
import {NoError} from './NoError';
import {SearchFilter} from './SearchFilter';
import {Order, SortButton} from './SortButton';

const useFormatTimestamp = () => {
    const formatDateTime = useDateFormatter();

    return (timestamp: number) =>
        formatDateTime(timestamp, {
            year: 'numeric',
            month: 'numeric',
            day: 'numeric',
            hour: 'numeric',
            minute: 'numeric',
            second: 'numeric',
        });
};

const sortingByTimestamp = (sortOrder: Order) => {
    if ('asc' === sortOrder) {
        return (errorA: ConnectionError, errorB: ConnectionError) => errorA.timestamp - errorB.timestamp;
    } else {
        return (errorA: ConnectionError, errorB: ConnectionError) => errorB.timestamp - errorA.timestamp;
    }
};

const filteringBySearchValue = (searchValue: string) => {
    return (error: ConnectionError) => new RegExp(searchValue, 'i').test(JSON.stringify(error.content));
};

type Props = {
    errors: ConnectionError[];
};

const ErrorList: FC<Props> = ({errors}) => {
    const formatTimestamp = useFormatTimestamp();

    const [sortOrder, setSortOrder] = useState<Order>('desc');
    const [searchValue, setSearchValue] = useState<string>('');

    const sortedAndfilteredErrors = errors
        .sort(sortingByTimestamp(sortOrder))
        .filter(filteringBySearchValue(searchValue));

    return (
        <>
            <SearchFilter value={searchValue} onSearch={setSearchValue} resultCount={sortedAndfilteredErrors.length} />

            {errors.length > 0 ? (
                <Table>
                    <thead>
                        <TableHeaderRow>
                            <TableHeaderCell>
                                <SortButton order={sortOrder} onSort={setSortOrder}>
                                    <Translate id='akeneo_connectivity.connection.error_management.connection_monitoring.error_list.date_time_column' />
                                </SortButton>
                            </TableHeaderCell>
                            <TableHeaderCell>
                                <Translate id='akeneo_connectivity.connection.error_management.connection_monitoring.error_list.content_column' />
                            </TableHeaderCell>
                        </TableHeaderRow>
                    </thead>
                    <tbody>
                        {sortedAndfilteredErrors.map(error => (
                            <TableRow key={error.id}>
                                <DateTimeCell collapsing>{formatTimestamp(error.timestamp)}</DateTimeCell>
                                <ErrorMessageCell>
                                    <strong>{error.content.message}</strong>
                                    <table>
                                        <tbody>
                                            {Object.entries(error.content)
                                                .filter(([key]) => 'message' !== key)
                                                .map(([key, value], i) => {
                                                    return (
                                                        <ErrorContentRow key={i}>
                                                            <ErrorContentKeyCell>{key}</ErrorContentKeyCell>
                                                            <td>: {JSON.stringify(value)}</td>
                                                        </ErrorContentRow>
                                                    );
                                                })}
                                        </tbody>
                                    </table>
                                </ErrorMessageCell>
                            </TableRow>
                        ))}
                    </tbody>
                </Table>
            ) : (
                <NoError />
            )}
        </>
    );
};

const DateTimeCell = styled(TableCell)`
    color: ${({theme}) => theme.color.grey140};
`;

const ErrorMessageCell = styled(TableCell)`
    color: ${({theme}) => theme.color.red100};
    white-space: pre-wrap;
`;

const ErrorContentRow = styled.tr`
    line-height: ${({theme}) => theme.fontSize.default};
`;

const ErrorContentKeyCell = styled.th`
    text-align: left;
    font-weight: normal;
`;

export {ErrorList};
