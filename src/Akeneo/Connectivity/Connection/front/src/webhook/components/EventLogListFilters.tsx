import {SectionTitle} from 'akeneo-design-system';
import React, {FC} from 'react';
import styled from 'styled-components';
import {useTranslate} from '../../shared/translate';
import {EventSubscriptionLogFilters, getFiltersConfig} from '../model/EventSubscriptionLogFilters';
import {EventLogDateTimeRangeFilter} from './DateRangeFilter/EventLogDateTimeRangeFilter';
import {EventLogLevelFilter} from './EventLogLevelFilter';
import SearchInput from './SearchInput';

const StyledSectionTitle = styled(SectionTitle)`
    margin-bottom: 18px;
`;

export const EventLogListFilters: FC<{
    filters: EventSubscriptionLogFilters;
    onChange: (filters: EventSubscriptionLogFilters) => void;
    total?: number;
}> = ({filters, onChange, total}) => {
    const translate = useTranslate();

    const filtersConfig = getFiltersConfig();

    return (
        <StyledSectionTitle>
            <SearchInput
                value={filters.text}
                onSearch={(searchText: string) => onChange({...filters, text: searchText})}
                placeholder={translate('akeneo_connectivity.connection.webhook.event_logs.list.search.placeholder')}
            />
            <SectionTitle.Spacer />
            <SectionTitle.Information>
                {undefined !== total
                    ? translate(
                          'akeneo_connectivity.connection.webhook.event_logs.list.search.total',
                          {total: total.toString()},
                          total
                      )
                    : ''}
            </SectionTitle.Information>
            <SectionTitle.Separator />
            <EventLogLevelFilter levels={filters.levels} onChange={levels => onChange({...filters, levels: levels})} />
            <EventLogDateTimeRangeFilter
                value={filters.dateTime}
                limit={filtersConfig.dateTime}
                isDirty={undefined !== filters.dateTime.start || undefined !== filters.dateTime.end}
                onChange={(start, end) =>
                    onChange({
                        ...filters,
                        dateTime: {start, end},
                    })
                }
                onReset={() =>
                    onChange({
                        ...filters,
                        dateTime: {},
                    })
                }
            />
        </StyledSectionTitle>
    );
};
