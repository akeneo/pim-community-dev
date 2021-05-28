import React, {FC, useContext, useState} from 'react';
import {Table, TableHeaderCell, TableHeaderRow, TableRow} from '../../../common';
import {ChannelContext, useChannel} from '../../../shared/channel';
import {FamilyContext, useFamily} from '../../../shared/family';
import {LocaleContext, useLocale} from '../../../shared/locale';
import {Translate} from '../../../shared/translate';
import {UserContext} from '../../../shared/user';
import {ConnectionError} from '../../model/ConnectionError';
import {ErrorDateTimeCell} from './ErrorDateTimeCell';
import {ErrorDetailsCell} from './ErrorDetailsCell';
import {ErrorMessageCell} from './ErrorMessageCell';
import {NoError} from './NoError';
import {SearchFilter} from './SearchFilter';
import {Order, SortButton} from './SortButton';

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

    const sortedAndfilteredErrors = errors
        .sort(sortingByTimestamp(sortOrder))
        .filter(filteringBySearchValue(searchValue));

    const userLocale = useContext(UserContext).get<string>('uiLocale');
    const {locales} = useLocale();
    const {families} = useFamily(userLocale);
    const {channels} = useChannel(userLocale);

    return (
        <>
            <SearchFilter value={searchValue} onSearch={setSearchValue} resultCount={sortedAndfilteredErrors.length} />

            {errors.length > 0 ? (
                <LocaleContext.Provider value={locales}>
                    <FamilyContext.Provider value={families}>
                        <ChannelContext.Provider value={channels}>
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
                        </ChannelContext.Provider>
                    </FamilyContext.Provider>
                </LocaleContext.Provider>
            ) : (
                <NoError />
            )}
        </>
    );
};
