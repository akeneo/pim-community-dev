import React, {FC} from 'react';
import styled from '../../../common/styled-with-theme';
import {useDateFormatter} from '../../../shared/formatter/use-date-formatter';
import {useNumberFormatter} from '../../../shared/formatter/use-number-formatter';
import {Translate, useTranslate} from '../../../shared/translate';
import {WeeklyChart} from './WeeklyChart';

type ChartData = {x: number; y: number; xLabel: string; yLabel: string};
type Props = {
    title: string;
    theme: 'blue' | 'green' | 'purple' | 'red';
    small?: true;
    weeklyAuditData: {
        daily: {
            [eventDate: string]: number;
        };
        weekly_total: number;
    };
};

export const WeeklyAuditChart: FC<Props> = ({title, theme, weeklyAuditData, small}: Props) => {
    const translate = useTranslate();
    const formatNumber = useNumberFormatter();
    const formatDate = useDateFormatter();

    const formatDateOptions: Intl.DateTimeFormatOptions = small
        ? {month: 'short', day: 'numeric'}
        : {weekday: 'long', month: 'short', day: 'numeric'};

    const chartData: ChartData[] = Object.entries(weeklyAuditData.daily).map(([date, value], index) => ({
        x: index,
        y: value,
        xLabel:
            index + 1 !== Object.keys(weeklyAuditData.daily).length
                ? formatDate(date, formatDateOptions)
                : translate('akeneo_connectivity.connection.dashboard.charts.legend.today'),
        yLabel: 0 === index ? '' : formatNumber(value),
    }));

    const chartOptions = {height: 283, width: small ? 491 : 1000};

    return (
        <>
            <Title>{title}</Title>
            <SubTitle>
                <Translate id='akeneo_connectivity.connection.dashboard.charts.legend.during_the_last_seven_days' />
                &nbsp;
                <Count>{formatNumber(weeklyAuditData.weekly_total)}</Count>
            </SubTitle>
            <WeeklyChart chartOptions={chartOptions} data={chartData} theme={theme} />
        </>
    );
};

const Title = styled.div`
    color: ${({theme}) => theme.color.purple100};
    display: block;
    font-size: ${({theme}) => theme.fontSize.bigger};
    font-weight: bold;
    line-height: 1.2em;
    text-transform: uppercase;
    padding-top: 20px;
    padding-bottom: 5px;
`;

const SubTitle = styled.div`
    color: ${({theme}) => theme.color.grey140};
    font-size: ${({theme}) => theme.fontSize.big};
    font-weight: bold;
    line-height: 1.2em;
    padding-bottom: 20px;
`;

const Count = styled.span`
    color: ${({theme}) => theme.color.purple100};
`;
