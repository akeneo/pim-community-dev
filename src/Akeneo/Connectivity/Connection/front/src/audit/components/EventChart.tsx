import React, {FC, ReactNode, useContext, useEffect, useState} from 'react';
import styled from 'styled-components';
import {VictoryThemeDefinition} from 'victory';
import {Section} from '../../common';
import {AuditEventType} from '../../model/audit-event-type.enum';
import {useNumberFormatter} from '../../shared/formatter/use-number-formatter';
import {useTranslate} from '../../shared/translate';
import {UserContext} from '../../shared/user';
import {useFetchConnectionsAuditData} from '../api-hooks/use-fetch-connections-audit-data';
import {useDashboardState} from '../dashboard-context';
import {Chart} from './Chart';
import {ConnectionSelect} from './ConnectionSelect';

type Props = {
    title: ReactNode;
    eventType: AuditEventType;
    theme: VictoryThemeDefinition;
};
type ChartEntry = {
    x: number;
    y: number;
    xLabel: string;
    yLabel: string;
};

const EventChartContainer = styled.div`
    padding-bottom: 25px;
`;

export const EventChart: FC<Props> = ({title, eventType, theme}: Props) => {
    const state = useDashboardState();

    const [selectedConnectionCode, setSelectedConnectionCode] = useState<string>();
    useEffect(() => {
        if (0 === Object.keys(state.sourceConnections).length) {
            setSelectedConnectionCode(undefined);
        } else if (Object.keys(state.sourceConnections).length > 0 && undefined === selectedConnectionCode) {
            setSelectedConnectionCode('<all>');
        }
    }, [state.sourceConnections, selectedConnectionCode]);

    const connectionsAuditData = useFetchConnectionsAuditData(eventType);
    const [chartData, setChartData] = useState<Array<ChartEntry>>();
    const uiLocale = useContext(UserContext).get('uiLocale');
    const formatNumber = useNumberFormatter();
    const translate = useTranslate();
    useEffect(() => {
        setChartData(undefined);
        if (undefined === selectedConnectionCode || undefined === connectionsAuditData[selectedConnectionCode]) {
            return;
        }

        const selectedConnectionAuditData = connectionsAuditData[selectedConnectionCode];
        const numberOfData = Object.keys(selectedConnectionAuditData).length;
        const chartData = Object.entries(selectedConnectionAuditData).map(
            ([date, value], index): ChartEntry => {
                const xLabel = new Intl.DateTimeFormat(uiLocale.replace('_', '-'), {
                    weekday: 'long',
                    month: 'short',
                    day: 'numeric',
                    timeZone: 'UTC',
                }).format(new Date(date));

                    return {
                        x: index,
                        y: value,
                        xLabel:
                            index + 1 !== numberOfData
                                ? xLabel
                                : translate('akeneo_connectivity.connection.dashboard.charts.legend.today'),
                        yLabel: 0 === index ? '' : formatNumber(value),
                    };
            }
        );

        setChartData(chartData);
    }, [uiLocale, formatNumber, translate, connectionsAuditData, selectedConnectionCode]);

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
                <ConnectionSelect connections={connections} onChange={code => setSelectedConnectionCode(code)} />
            </Section>

            {chartData && <Chart data={chartData} theme={theme} />}
        </EventChartContainer>
    );
};
