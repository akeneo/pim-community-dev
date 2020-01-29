import React from 'react';
import {DomainTuple, VictoryAxis, VictoryChart, VictoryLine, VictoryScatter, VictoryThemeDefinition} from 'victory';
import {darkGrey, grey, lightGrey, lightGreyStroke, purple} from '../event-chart-themes';

const yAxeTheme = {
    tickLabels: {
        fill: 'none',
    },
    axis: lightGreyStroke,
    grid: lightGreyStroke,
};
const daysAxeTheme = {
    tickLabels: {
        fontSize: 11,
        fontFamily: 'Lato',
        fontWeight: ({tickValue}: {tickValue: any}) => (7 === tickValue ? 'bold' : 'normal'),
        fill: ({tickValue}: {tickValue: any}) => (7 === tickValue ? purple : grey),
    },
    axis: {
        stroke: 'none',
    },
    grid: {
        stroke: 'none',
    },
};
const gridYAxesTheme = {
    tickLabels: {
        fill: 'none',
    },
    grid: {
        stroke: 'none',
    },
    axis: lightGreyStroke,
};

type ChartData = {x: number; y: number; xLabel: string; yLabel: string};

export const Chart = ({data, theme}: {data: ChartData[]; theme: VictoryThemeDefinition}) => {
    let yMax = data.reduce((maxY, {y}) => (y > maxY ? y : maxY), 0);
    let yMin = data.reduce((minY, {y}) => (y < minY ? y : minY), yMax);

    // If yMin & yMax are equals: arbitrary override yMax value to be superior than yMin.
    if (yMin === 0 && yMax === 0) {
        yMax = 1;
    }
    // yMax & yMin are differents from 0 (but equals): arbitrary center the value between yMin & yMax.
    if (yMax - yMin === 0) {
        yMax += 1;
        yMin -= 1;
    }

    // Define the chart visual domain, add a margin of 20% of the data values below yMin and above yMax.
    const yMaxDomain = yMax + (yMax - yMin) / 5;
    const yMinDomain = yMin - (yMax - yMin) / 5;

    // Define the absolute value used to draw each axe (total of 7).
    const step = (yMaxDomain - yMinDomain) / 7;
    const yGridAxes: number[] = [
        yMinDomain,
        yMinDomain + 1 * step,
        yMinDomain + 2 * step,
        yMinDomain + 3 * step,
        yMinDomain + 4 * step,
        yMinDomain + 5 * step,
        yMinDomain + 6 * step,
        yMaxDomain,
    ];

    // The first X value need to be hidden. It is used to see the hidden start of the chart.
    const xDaysValues = data.map(({xLabel}) => xLabel).slice(1);

    const yLabels = ({datum}: {datum: ChartData}) => datum.yLabel;

    const domain: {
        x: DomainTuple;
        y: DomainTuple;
    } = {
        x: [0.5, 7.5],
        y: [yMinDomain, yMaxDomain],
    };

    // The rendering order is based on the elements order. Axes must be first to be draw in background.
    return (
        <VictoryChart
            height={300}
            width={1000}
            padding={0}
            domain={domain}
            theme={theme}
            style={{
                parent: {
                    borderBottom: `1px solid ${darkGrey}`,
                    borderLeft: `1px solid ${lightGrey}`,
                    borderRight: `1px solid ${lightGrey}`,
                },
            }}
        >
            <VictoryAxis padding={0} offsetY={25} tickValues={xDaysValues} style={daysAxeTheme} />
            <VictoryAxis dependentAxis tickValues={yGridAxes} style={yAxeTheme} />
            {[1.5, 2.5, 3.5, 4.5, 5.5, 6.5, 7.5].map((value, index) => {
                return <VictoryAxis dependentAxis key={index} style={gridYAxesTheme} axisValue={value} />;
            })}
            <VictoryLine data={data} interpolation='monotoneX' />
            <VictoryScatter data={data} labels={yLabels} size={7} />
        </VictoryChart>
    );
};
