import React from 'react';
import {EventChart} from './EventChart';
import {AuditEventType} from '../../model/audit-event-type.enum';
import {blueTheme, purpleTheme} from '../event-chart-themes';
import {Translate} from '../../shared/translate';
import {Section} from '../../common';
import {ConnectionSelect} from './ConnectionSelect';
import styled from 'styled-components';
import useConnectionSelect from '../useConnectionSelector';
import {FlowType} from '../../model/flow-type.enum';

const DataSourceChartsContainer = styled.div`
    padding-bottom: 25px;
    display: block;
`;
const ChartsContainer = styled.div`
    display: flex;
    justify-content: space-between;
    flex-direction: row;
`;
const EventChartContainer = styled.div`
    width: 49%;
`;

export const DataSourceCharts = () => {
    const [connections, selectedConnectionCode, setSelectedConnectionCode] = useConnectionSelect(FlowType.DATA_SOURCE);

    return (
        <DataSourceChartsContainer>
            <Section title={<Translate id='akeneo_connectivity.connection.dashboard.charts.inbound' />}>
                <ConnectionSelect connections={connections} onChange={code => setSelectedConnectionCode(code)} />
            </Section>
            <ChartsContainer>
                <EventChartContainer>
                    <EventChart
                        eventType={AuditEventType.PRODUCT_CREATED}
                        theme={purpleTheme}
                        title={
                            <Translate id='akeneo_connectivity.connection.dashboard.charts.number_of_products_created' />
                        }
                        selectedConnectionCode={selectedConnectionCode}
                        dateFormat={{month: 'short', day: 'numeric'}}
                        chartOptions={{height: 283, width: 491}}
                    />
                </EventChartContainer>
                <EventChartContainer>
                    <EventChart
                        eventType={AuditEventType.PRODUCT_UPDATED}
                        theme={blueTheme}
                        title={
                            <Translate id='akeneo_connectivity.connection.dashboard.charts.number_of_products_updated' />
                        }
                        selectedConnectionCode={selectedConnectionCode}
                        dateFormat={{month: 'short', day: 'numeric'}}
                        chartOptions={{height: 283, width: 491}}
                    />
                </EventChartContainer>
            </ChartsContainer>
        </DataSourceChartsContainer>
    );
};
