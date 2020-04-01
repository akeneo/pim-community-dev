import React from 'react';
import {EventChart} from './EventChart';
import {AuditEventType} from '../../model/audit-event-type.enum';
import {blueTheme, purpleTheme} from '../event-chart-themes';
import {Translate} from '../../shared/translate';
import {Section} from '../../common';
import {ConnectionSelect} from './ConnectionSelect';
import styled from 'styled-components';
import useConnectionSelect from '../useConnectionSelect';
import {FlowType} from '../../model/flow-type.enum';
import {NoConnection} from './NoConnection';
import {PropsWithTheme} from '../../common/theme';

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
const NoConnectionContainer = styled.div`
    border: 1px solid ${({theme}: PropsWithTheme) => theme.color.grey70};
    padding-bottom: 20px;
    margin-top: 20px;
`;

export const DataSourceCharts = () => {
    const [connections, selectedConnectionCode, setSelectedConnectionCode] = useConnectionSelect(FlowType.DATA_SOURCE);
    const noConnection = 0 === connections.length;

    return (
        <DataSourceChartsContainer>
            <Section title={<Translate id='akeneo_connectivity.connection.dashboard.charts.inbound' />}>
                {!noConnection && (
                    <ConnectionSelect
                        connections={connections}
                        onChange={code => setSelectedConnectionCode(code)}
                        label={
                            <Translate id='akeneo_connectivity.connection.dashboard.connection_selector.title.source' />
                        }
                    />
                )}
            </Section>
            {noConnection ? (
                <NoConnectionContainer>
                    <NoConnection flowType={FlowType.DATA_SOURCE} />
                </NoConnectionContainer>
            ) : (
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
            )}
        </DataSourceChartsContainer>
    );
};
