import React, {FC, ReactNode, useEffect, useState} from 'react';
import {AvailableColors, Chart} from './Chart';
import {AppSelect} from './AppSelect';
import {useDashboardState} from '../dashboard-state-context';
import {Section} from '../../common';
import {useTranslate} from '../../shared/translate';
import {AuditEventType} from '../../../domain/audit/audit-event-type.enum';
import {useFetchSourceAppsEvent} from '../api-hooks/use-fetch-source-apps-event';
import {useDateFormatter} from '../../shared/date-formatter/use-date-formatter';
import styled from 'styled-components';

type Props = {
    title: ReactNode;
    eventType: AuditEventType;
};

const EventChartContainer = styled.div`
    padding-bottom: 25px;
`;

const getChartColor: (eventType: AuditEventType) => AvailableColors = eventType => {
    switch (eventType) {
        case AuditEventType.PRODUCT_CREATED:
            return AvailableColors.PURPLE;
            break;

        case AuditEventType.PRODUCT_UPDATED:
            return AvailableColors.BLUE;
            break;
    }

    return AvailableColors.PURPLE;
};

export const EventChart: FC<Props> = ({title, eventType}: Props) => {
    const [state] = useDashboardState();
    const formatDate = useDateFormatter();
    const translate = useTranslate();

    const [selectedAppCode, setSelectedAppCode] = useState();
    useEffect(() => {
        if (0 === Object.keys(state.sourceApps).length) {
            setSelectedAppCode(undefined);
        } else if (Object.keys(state.sourceApps).length > 0 && undefined === selectedAppCode) {
            setSelectedAppCode(Object.values(state.sourceApps)[0].code);
        }
    }, [state.sourceApps]);

    const appsData = useFetchSourceAppsEvent(eventType);

    const [chartData, setChartData] = useState();
    useEffect(() => {
        setChartData(undefined);

        if (undefined === appsData[selectedAppCode]) {
            return;
        }
        const selectedApp = appsData[selectedAppCode];
        const numberOfData = Object.keys(selectedApp).length;
        const chartData = Object.entries(selectedApp).map(([date, value], index) => ({
            x: index,
            y: value,
            xLabel:
                index + 1 !== numberOfData
                    ? formatDate(date, {weekday: 'long', month: 'short', day: 'numeric'})
                    : translate('akeneo_apps.dashboard.charts.legend.today'),
            yLabel: value.toString(),
        }));

        setChartData(chartData);
    }, [appsData, selectedAppCode]);

    return (
        <EventChartContainer>
            <Section title={title}>
                <AppSelect
                    apps={Object.values(state.sourceApps)}
                    code={selectedAppCode}
                    onChange={code => setSelectedAppCode(code)}
                />
            </Section>

            {chartData ? <Chart data={chartData} color={getChartColor(eventType)} /> : <>...</>}
        </EventChartContainer>
    );
};
