import React, {FC, ReactNode, useEffect, useState} from 'react';
import styled from 'styled-components';
import {VictoryThemeDefinition} from 'victory';
import {Section} from '../../common';
import {AuditEventType} from '../../model/audit-event-type.enum';
import {useDateFormatter} from '../../shared/formatter/use-date-formatter';
import {useNumberFormatter} from '../../shared/formatter/use-number-formatter';
import {useTranslate} from '../../shared/translate';
import {useFetchConnectionsAuditData} from '../api-hooks/use-fetch-connections-audit-data';
import {useDashboardState} from '../dashboard-context';
import {Chart} from './Chart';
import {ConnectionSelect} from './ConnectionSelect';

type Props = {
    title: ReactNode;
    eventType: AuditEventType;
    theme: VictoryThemeDefinition;
};

const EventChartContainer = styled.div`
    padding-bottom: 25px;
`;

export const EventChart: FC<Props> = ({title, eventType, theme}: Props) => {
    const state = useDashboardState();

    const [selectedConnectionCode, setSelectedConnectionCode] = useState();
    useEffect(() => {
        if (0 === Object.keys(state.sourceConnections).length) {
            setSelectedConnectionCode(undefined);
        } else if (Object.keys(state.sourceConnections).length > 0 && undefined === selectedConnectionCode) {
            setSelectedConnectionCode('<all>');
        }
    }, [state.sourceConnections, selectedConnectionCode]);

    const connectionsAuditData = useFetchConnectionsAuditData(eventType);
    const [chartData, setChartData] = useState();
    const formatDate = useDateFormatter();
    const formatNumber = useNumberFormatter();
    const translate = useTranslate();
    useEffect(() => {
        setChartData(undefined);
        if (undefined === connectionsAuditData[selectedConnectionCode]) {
            return;
        }

        const selectedConnectionAuditData = connectionsAuditData[selectedConnectionCode];
        const numberOfData = Object.keys(selectedConnectionAuditData).length;
        const chartData = Object.entries(selectedConnectionAuditData).map(([date, value], index) => ({
            x: index,
            y: value,
            xLabel:
                index + 1 !== numberOfData
                    ? formatDate(date, {weekday: 'long', month: 'short', day: 'numeric'})
                    : translate('akeneo_connectivity.connection.dashboard.charts.legend.today'),
            yLabel: 0 === index ? '' : formatNumber(value),
        }));

        setChartData(chartData);
    }, [formatDate, translate, connectionsAuditData, selectedConnectionCode]);

    const connections = Object.values(state.sourceConnections);
    connections.unshift({
        code: '<all>',
        label: translate('akeneo_connectivity.connection.dashboard.connection_selector.all'),
        flowType: connections[0].flowType,
        image: null,
    });

    return (
        <EventChartContainer>
            <Section title={title}>
                <ConnectionSelect
                    connections={connections}
                    code={selectedConnectionCode}
                    onChange={code => setSelectedConnectionCode(code)}
                />
            </Section>

            {chartData && <Chart data={chartData} theme={theme} />}
        </EventChartContainer>
    );
};
