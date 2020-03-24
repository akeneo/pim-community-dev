import React, {FC, ReactNode, useEffect, useState} from 'react';
import styled from 'styled-components';
import {VictoryChartProps, VictoryThemeDefinition} from 'victory';
import {PropsWithTheme} from '../../common/theme';
import {AuditEventType} from '../../model/audit-event-type.enum';
import {useDateFormatter} from '../../shared/formatter/use-date-formatter';
import {useNumberFormatter} from '../../shared/formatter/use-number-formatter';
import {Translate, useTranslate} from '../../shared/translate';
import {useFetchConnectionsAuditData} from '../api-hooks/use-fetch-connections-audit-data';
import {Chart} from './Chart';

type Props = {
    title: ReactNode;
    eventType: AuditEventType;
    theme: VictoryThemeDefinition;
    dateFormat: Intl.DateTimeFormatOptions;
    selectedConnectionCode?: string;
    chartOptions?: VictoryChartProps;
};
type ChartEntry = {
    x: number;
    y: number;
    xLabel: string;
    yLabel: string;
};

export const EventChart: FC<Props> = ({
    title,
    eventType,
    theme,
    dateFormat,
    selectedConnectionCode,
    chartOptions,
}: Props) => {
    const connectionsAuditData = useFetchConnectionsAuditData(eventType);
    const [chartData, setChartData] = useState<Array<ChartEntry>>();
    const formatDate = useDateFormatter();
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
            ([date, value], index): ChartEntry => ({
                x: index,
                y: value,
                xLabel:
                    index + 1 !== numberOfData
                        ? formatDate(date, dateFormat)
                        : translate('akeneo_connectivity.connection.dashboard.charts.legend.today'),
                yLabel: 0 === index ? '' : formatNumber(value),
            })
        );

        setChartData(chartData);
    }, [formatDate, translate, connectionsAuditData, selectedConnectionCode]);

    const total = (selectedConnectionCode && connectionsAuditData[selectedConnectionCode]?.weekly_total) || 0;

    return (
        <>
            {chartData && (
                <>
                    <Title>{title}</Title>
                    <SubTitle>
                        <Translate id='akeneo_connectivity.connection.dashboard.charts.legend.during_the_last_seven_days' />
                        &nbsp;
                        <Count>{formatNumber(total)}</Count>
                    </SubTitle>
                    <Chart chartOptions={chartOptions} data={chartData} theme={theme} />
                </>
            )}
        </>
    );
};

const Title = styled.div`
    color: ${({theme}: PropsWithTheme) => theme.color.purple100};
    display: block;
    font-size: ${({theme}: PropsWithTheme) => theme.fontSize.bigger};
    font-weight: bold;
    line-height: 21px;
    text-transform: uppercase;
    padding-top: 20px;
    padding-bottom: 5px;
`;

const SubTitle = styled.div`
    color: ${({theme}: PropsWithTheme) => theme.color.grey140};
    font-size: ${({theme}: PropsWithTheme) => theme.fontSize.bigger};
    font-weight: bold;
    line-height: 21px;
    padding-bottom: 20px;
`;

const Count = styled.span`
    color: ${({theme}: PropsWithTheme) => theme.color.purple100};
`;
