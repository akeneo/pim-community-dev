import {SectionTitle} from 'akeneo-design-system';
import React, {FC} from 'react';
import {useTranslate} from '../../shared/translate';
import styled from 'styled-components';
import {EventLogLevelFilter} from './EventLogLevelFilter';
import {Filters} from '../hooks/api/use-infinite-event-subscription-logs';

const StyledSectionTitle = styled(SectionTitle)`
    margin-bottom: 18px;
`;

export const EventLogListFilters: FC<{
    filters: Filters,
    onChange: (filters: Filters) => void,
    total?: number,
}> = ({filters, onChange, total}) => {
    const translate = useTranslate();

    return (
        <StyledSectionTitle>
            <SectionTitle.Title>
                {translate('akeneo_connectivity.connection.webhook.event_logs.list.search.title')}
            </SectionTitle.Title>
            <SectionTitle.Spacer/>
            <SectionTitle.Information>
                {translate('akeneo_connectivity.connection.webhook.event_logs.list.search.total', {total: total ? total.toString() : '0'}, total)}
            </SectionTitle.Information>
            <SectionTitle.Separator/>
            <EventLogLevelFilter
                levels={filters.levels}
                onChange={levels => onChange({...filters, levels: levels})}
            />
                {/*onChange={levels => onChange({...filters, levels: levels})}*/}
            {/*<Button>Action</Button>*/}
            {/*<Button>Action</Button>*/}
            {/*<IconButton icon={<MoreIcon />} title="More" />*/}
        </StyledSectionTitle>
    );
};
