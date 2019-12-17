import React, {useState, useEffect, ReactNode, FC} from 'react';
import {Chart} from './Chart';
import {AppSelect} from './AppSelect';
import {useDashboardState} from '../dashboard-state-context';
import {Section} from '../../common';
import {AuditEventType} from '../../../domain/audit/audit-event-type.enum';
import {useFetchSourceAppsEvent} from '../api-hooks/use-fetch-source-apps-event';

type Props = {
    title: ReactNode;
    eventType: AuditEventType;
};

export const EventChart: FC<Props> = ({title, eventType}) => {
    const [state] = useDashboardState();

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

        const chartData = Object.entries(appsData[selectedAppCode]).map(([date, value], index) => ({
            x: index,
            y: value,
            xLabel: date,
            yLabel: value.toString(),
        }));

        setChartData(chartData);
    }, [appsData, selectedAppCode]);

    return (
        <>
            <Section title={title}>
                <AppSelect
                    apps={Object.values(state.sourceApps)}
                    code={selectedAppCode}
                    onChange={code => setSelectedAppCode(code)}
                />
            </Section>

            {chartData ? <Chart data={chartData} /> : <>...</>}
        </>
    );
};
