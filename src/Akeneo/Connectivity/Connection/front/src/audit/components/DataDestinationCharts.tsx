import React from 'react';
import {Translate} from '../../shared/translate';
import {Section} from '../../common';
import {ConnectionSelect} from '../components/ConnectionSelect';
import {EventChart} from '../components/EventChart';
import {AuditEventType} from '../../model/audit-event-type.enum';
import {greenTheme} from '../event-chart-themes';
import styled from 'styled-components';
import useConnectionSelect from '../useConnectionSelector';
import {FlowType} from '../../model/flow-type.enum';

const DataDestinationChartsContainer = styled.div`
    padding-bottom: 25px;
    display: block;
`;

export const DataDestinationCharts = () => {
    const [connections, selectedConnectionCode, setSelectedConnectionCode] = useConnectionSelect(
        FlowType.DATA_DESTINATION
    );

    return (
        <DataDestinationChartsContainer>
            <Section title={<Translate id='akeneo_connectivity.connection.dashboard.charts.outbound' />}>
                <ConnectionSelect connections={connections} onChange={code => setSelectedConnectionCode(code)} />
            </Section>

            <EventChart
                eventType={AuditEventType.PRODUCT_READ}
                theme={greenTheme}
                title={<Translate id='akeneo_connectivity.connection.dashboard.charts.number_of_products_sent' />}
                selectedConnectionCode={selectedConnectionCode}
                dateFormat={{weekday: 'long', month: 'short', day: 'numeric'}}
                chartOptions={{height: 283, width: 1000}}
            />
        </DataDestinationChartsContainer>
    );
};
