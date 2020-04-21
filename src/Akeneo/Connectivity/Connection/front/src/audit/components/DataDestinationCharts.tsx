import React from 'react';
import {Section} from '../../common';
import styled from '../../common/styled-with-theme';
import {AuditEventType} from '../../model/audit-event-type.enum';
import {FlowType} from '../../model/flow-type.enum';
import {Translate} from '../../shared/translate';
import {ConnectionSelect} from '../components/ConnectionSelect';
import {EventChart} from '../components/EventChart';
import {greenTheme} from '../event-chart-themes';
import useConnectionSelect from '../useConnectionSelect';
import {NoConnection} from './NoConnection';

const DataDestinationChartsContainer = styled.div`
    padding-bottom: 25px;
    display: block;
`;
const NoConnectionContainer = styled.div`
    border: 1px solid ${({theme}) => theme.color.grey60};
    padding-bottom: 20px;
    margin-top: 20px;
`;

export const DataDestinationCharts = () => {
    const [connections, selectedConnectionCode, setSelectedConnectionCode] = useConnectionSelect(
        FlowType.DATA_DESTINATION
    );
    const noConnection = 0 === connections.length;

    return (
        <DataDestinationChartsContainer>
            <Section title={<Translate id='akeneo_connectivity.connection.dashboard.charts.outbound' />}>
                {!noConnection && (
                    <ConnectionSelect
                        connections={connections}
                        onChange={code => setSelectedConnectionCode(code)}
                        label={
                            <Translate id='akeneo_connectivity.connection.dashboard.connection_selector.title.destination' />
                        }
                    />
                )}
            </Section>

            {noConnection ? (
                <NoConnectionContainer>
                    <NoConnection flowType={FlowType.DATA_DESTINATION} />
                </NoConnectionContainer>
            ) : (
                <EventChart
                    eventType={AuditEventType.PRODUCT_READ}
                    theme={greenTheme}
                    title={<Translate id='akeneo_connectivity.connection.dashboard.charts.number_of_products_sent' />}
                    selectedConnectionCode={selectedConnectionCode}
                    dateFormat={{weekday: 'long', month: 'short', day: 'numeric'}}
                    chartOptions={{height: 283, width: 1000}}
                />
            )}
        </DataDestinationChartsContainer>
    );
};
