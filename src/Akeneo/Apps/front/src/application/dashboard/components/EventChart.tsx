import React, {useState, useEffect} from 'react';
import {Chart} from './Chart';
import {AppSelect} from './AppSelect';
import {useAppsState} from '../../dashboard/app-state-context';
import {Section} from '../../common';
import {Translate} from '../../shared/translate';

export const EventChart = () => {
    const [apps] = useAppsState();
    const [code, setCode] = useState();

    useEffect(() => {
        if (0 === Object.keys(apps).length) {
            setCode(undefined);
        } else if (Object.keys(apps).length > 0 && undefined === code) {
            setCode(Object.values(apps)[0].code);
        }
    }, [apps]);

    const [data, setData] = useState();

    useEffect(() => {
        const appData = [
            {date: 'Tuesday, Nov. 26', value: Math.round(Math.random() * 1000)},
            {date: 'Wednesday, Nov. 27', value: Math.round(Math.random() * 1000)},
            {date: 'Thursday, Nov. 28', value: Math.round(Math.random() * 1000)},
            {date: 'Friday, Nov. 29', value: Math.round(Math.random() * 1000)},
            {date: 'Saturday, Nov. 30', value: Math.round(Math.random() * 1000)},
            {date: 'Sunday, Dec. 1', value: Math.round(Math.random() * 1000)},
            {date: 'Today', value: Math.round(Math.random() * 1000)},
        ];

        const chartData = appData.map(({date, value}, index) => ({
            x: index,
            y: value,
            xLabel: date,
            yLabel: value.toString(),
        }));

        setData(chartData);
    }, [code]);

    return (
        <>
            <Section title={<Translate id='akeneo_apps.dashboard.charts.number_of_products_created' />} />

            <AppSelect apps={Object.values(apps)} code={code} onChange={code => setCode(code)} />
            {data ? <Chart data={data} /> : <>Loading...</>}
        </>
    );
};
