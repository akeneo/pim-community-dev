import React, { FC, useState } from 'react';
import { Table, TableHeaderCell, TableHeaderRow, TableRow } from '../../../common';
import { Translate } from '../../../shared/translate';
import { LocaleContext } from '../../../shared/locale/locale-context';
import { useLocale } from '../../../shared/locale/use-locale';
import { ConnectionError } from '../../model/ConnectionError';
import { ErrorDateTimeCell } from './ErrorDateTimeCell';
import { ErrorDetailsCell } from './ErrorDetailsCell';
import { ErrorMessageCell } from './ErrorMessageCell';
import { NoError } from './NoError';
import { SearchFilter } from './SearchFilter';
import { Order, SortButton } from './SortButton';

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

export const ErrorList: FC<Props> = ({errors}) => {
    const [sortOrder, setSortOrder] = useState<Order>('desc');
    const [searchValue, setSearchValue] = useState<string>('');

    const {locales} = useLocale();

    const sortedAndfilteredErrors = errors
        .sort(sortingByTimestamp(sortOrder))
        .filter(filteringBySearchValue(searchValue));

    return (
        <>
            <SearchFilter value={searchValue} onSearch={setSearchValue} resultCount={sortedAndfilteredErrors.length} />

            {errors.length > 0 ? (
                <LocaleContext.Provider value={locales}>
                    <Table>
                        <thead>
                            <TableHeaderRow>
                                <TableHeaderCell>
                                    <SortButton order={sortOrder} onSort={setSortOrder}>
                                        <Translate id='akeneo_connectivity.connection.error_management.connection_monitoring.error_list.date_time_column.title' />
                                    </SortButton>
                                </TableHeaderCell>
                                <TableHeaderCell>
                                    <Translate id='akeneo_connectivity.connection.error_management.connection_monitoring.error_list.content_column.title' />
                                </TableHeaderCell>
                                <TableHeaderCell>
                                    <Translate id='akeneo_connectivity.connection.error_management.connection_monitoring.error_list.details_column.title' />
                                </TableHeaderCell>
                            </TableHeaderRow>
                        </thead>
                        <tbody>
                            {sortedAndfilteredErrors.map(error => (
                                <TableRow key={error.id}>
                                    <ErrorDateTimeCell timestamp={error.timestamp} />
                                    <ErrorMessageCell content={error.content} />
                                    <ErrorDetailsCell content={error.content} />
                                </TableRow>
                            ))}
                        </tbody>
                    </Table>
                </LocaleContext.Provider>
            ) : (
                <NoError />
            )}
        </>
    );
};
