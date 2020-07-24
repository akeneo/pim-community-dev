import React from 'react';
import {Section} from '../../common';
import {Loading} from '../../common/components';
import styled from '../../common/styled-with-theme';
import {FlowType} from '../../model/flow-type.enum';
import {useTranslate} from '../../shared/translate';
import {useDashboardState} from '../dashboard-context';
import useConnectionSelect from '../hooks/useConnectionSelect';
import {WeeklyAuditChart} from './Chart/WeeklyAuditChart';
import {ConnectionSelect} from './ConnectionSelect';
import {NoConnection} from './NoConnection';

export const DataSourceCharts = () => {
    const translate = useTranslate();

    const {connections, connectionCode, selectConnectionCode} = useConnectionSelect(FlowType.DATA_SOURCE);
    const {events} = useDashboardState();

    if (0 === connections.filter(connection => connection.code !== '<all>').length) {
        return (
            <DataSourceChartsContainer>
                <Section title={translate('akeneo_connectivity.connection.dashboard.charts.inbound')} />
                <NoConnectionContainer>
                    <NoConnection small flowType={FlowType.DATA_SOURCE} />
                </NoConnectionContainer>
            </DataSourceChartsContainer>
        );
    }

    return (
        <DataSourceChartsContainer>
            <Section title={translate('akeneo_connectivity.connection.dashboard.charts.inbound')}>
                <ConnectionSelect
                    connections={connections}
                    onChange={code => selectConnectionCode(code!)}
                    label={translate('akeneo_connectivity.connection.dashboard.connection_selector.title.source')}
                />
            </Section>
            <ChartsContainer>
                <EventChartContainer>
                    {events.product_created[connectionCode] ? (
                        <WeeklyAuditChart
                            small
                            theme='purple'
                            title={translate(
                                'akeneo_connectivity.connection.dashboard.charts.number_of_products_created'
                            )}
                            weeklyAuditData={events.product_created[connectionCode]}
                        />
                    ) : (
                        <Loading />
                    )}
                </EventChartContainer>
                <EventChartContainer>
                    {events.product_updated[connectionCode] ? (
                        <WeeklyAuditChart
                            small
                            theme='blue'
                            title={translate(
                                'akeneo_connectivity.connection.dashboard.charts.number_of_products_updated'
                            )}
                            weeklyAuditData={events.product_updated[connectionCode]}
                        />
                    ) : (
                        <Loading />
                    )}
                </EventChartContainer>
            </ChartsContainer>
        </DataSourceChartsContainer>
    );
};

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
    border: 1px solid ${({theme}) => theme.color.grey60};
    padding-bottom: 20px;
    margin-top: 20px;
`;
