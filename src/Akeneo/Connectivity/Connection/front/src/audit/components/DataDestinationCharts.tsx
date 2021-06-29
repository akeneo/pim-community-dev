import React from 'react';
import {Loading} from '../../common';
import styled from '../../common/styled-with-theme';
import {FlowType} from '../../model/flow-type.enum';
import {Translate, useTranslate} from '../../shared/translate';
import {ConnectionSelect} from '../components/ConnectionSelect';
import {useDashboardState} from '../dashboard-context';
import useConnectionSelect from '../hooks/useConnectionSelect';
import {WeeklyAuditChart} from './Chart/WeeklyAuditChart';
import {NoConnection} from './NoConnection';
import {SectionTitle} from 'akeneo-design-system';

export const DataDestinationCharts = () => {
    const translate = useTranslate();

    const {connections, connectionCode, selectConnectionCode} = useConnectionSelect(FlowType.DATA_DESTINATION);
    const {events} = useDashboardState();

    if (0 === connections.filter(connection => connection.code !== '<all>').length) {
        return (
            <DataDestinationChartsContainer>
                <SectionTitle>
                    <SectionTitle.Title>
                        <Translate id='akeneo_connectivity.connection.dashboard.charts.outbound' />
                    </SectionTitle.Title>
                </SectionTitle>
                <NoConnectionContainer>
                    <NoConnection small flowType={FlowType.DATA_DESTINATION} />
                </NoConnectionContainer>
            </DataDestinationChartsContainer>
        );
    }

    return (
        <DataDestinationChartsContainer>
            <SectionTitle>
                <SectionTitle.Title>
                    <Translate id='akeneo_connectivity.connection.dashboard.charts.outbound' />
                </SectionTitle.Title>
                <SectionTitle.Spacer />
                <ConnectionSelect
                    connections={connections}
                    onChange={code => selectConnectionCode(code!)}
                    label={
                        <Translate id='akeneo_connectivity.connection.dashboard.connection_selector.title.destination' />
                    }
                />
            </SectionTitle>

            {events.product_read[connectionCode] ? (
                <WeeklyAuditChart
                    theme='green'
                    title={translate('akeneo_connectivity.connection.dashboard.charts.number_of_products_sent')}
                    weeklyAuditData={events.product_read[connectionCode]}
                />
            ) : (
                <Loading />
            )}
        </DataDestinationChartsContainer>
    );
};

const DataDestinationChartsContainer = styled.div`
    padding-bottom: 25px;
    display: block;
`;
const NoConnectionContainer = styled.div`
    border: 1px solid ${({theme}) => theme.color.grey60};
    padding-bottom: 20px;
    margin-top: 20px;
`;
