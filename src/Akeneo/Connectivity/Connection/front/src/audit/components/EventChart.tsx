import React, {FC, ReactNode, useContext, useEffect, useState} from 'react';
import {VictoryThemeDefinition} from 'victory';
import {AuditEventType} from '../../model/audit-event-type.enum';
import {useNumberFormatter} from '../../shared/formatter/use-number-formatter';
import {useTranslate} from '../../shared/translate';
import {UserContext} from '../../shared/user';
import {useFetchConnectionsAuditData} from '../api-hooks/use-fetch-connections-audit-data';
import {Chart} from './Chart';
import styled from 'styled-components';
import {PropsWithTheme} from '../../common/theme';

type Props = {
    title: ReactNode;
    eventType: AuditEventType;
    theme: VictoryThemeDefinition;
    selectedConnectionCode?: string;
};
type ChartEntry = {
    x: number;
    y: number;
    xLabel: string;
    yLabel: string;
};

const Title = styled.div`
    color: ${({theme}: PropsWithTheme) => theme.color.purple100};
    font-size: ${({theme}: PropsWithTheme) => theme.fontSize.bigger};
    line-height: 44px;
    text-transform: uppercase;
    font-weight: bold;
`;

const EventChartContainer = styled.div`
    width: 49%;
`;

export const EventChart: FC<Props> = ({title, eventType, theme, selectedConnectionCode}: Props) => {
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

        const selectedConnectionAuditData = connectionsAuditData[selectedConnectionCode].daily;
        const numberOfData = Object.keys(selectedConnectionAuditData).length;
        const chartData = Object.entries(selectedConnectionAuditData).map(
            ([date, value], index): ChartEntry => {
                const xLabel = new Intl.DateTimeFormat(uiLocale.replace('_', '-'), {
                    weekday: 'long',
                    month: 'short',
                    day: 'numeric',
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

    return (
        <>
            {chartData && (
                <EventChartContainer>
                    <Title>{title}</Title>
                    <Chart data={chartData} theme={theme} />
                </EventChartContainer>
            )}
        </>
    );
};
