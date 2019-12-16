import React from 'react';
import {VictoryAxis, VictoryChart, VictoryLine, VictoryScatter, VictoryThemeDefinition} from 'victory';

const themeBaseProps = {
    width: 350,
    height: 350,
    colorScale: [],
};
const theme: VictoryThemeDefinition = {
    axis: {
        style: {
            grid: {
                stroke: '#e8ebee',
                strokeDasharray: '0, 0',
            },
        },
        ...themeBaseProps,
    },
    line: {
        style: {
            data: {
                stroke: '#ded5e4',
            },
        },
        ...themeBaseProps,
    },
    scatter: {
        style: {
            labels: {
                fontSize: 13,
                fill: '#52267d',
            },
            data: {
                fill: '#52267d',
            },
        },
        ...themeBaseProps,
    },
};

type ChartData = {x: number; y: number; xLabel: string; yLabel: string};

export const Chart = ({data}: {data: ChartData[]}) => {
    // Find the maximum Y value
    const yMax = data.reduce((maxY, {y}) => (y > maxY ? y : maxY), 0);

    // Find the minimum Y value
    const yMin = data.reduce((minY, {y}) => (y < minY ? y : minY), yMax);

    // Add 20% to the maximum Y domain value (top padding)
    const yMaxDomain = yMax + (yMax - yMin) / 5;

    // Substract 20% to the minimum Y domain value (bottom padding)
    let yMinDomain = yMin - (yMax - yMin) / 5;
    if (yMinDomain < 0) {
        yMinDomain = 0;
    }
    // Define 1 tick for each X value
    const xTickValues = data.map((_, index) => index);

    // Define 1 tick per 20% of the 140% of the total domain
    const yTickValues = Array.from(new Array(7)).map((_, index) => ((yMaxDomain - yMinDomain) / 7) * index + 1);

    const yLabels = ({datum}: {datum: ChartData}) => datum.yLabel;

    console.log({
        yMax,
        yMinDomain,
        yMaxDomain,
    });

    return (
        <VictoryChart theme={theme} height={240} minDomain={{x: 0, y: yMinDomain}} maxDomain={{x: 7, y: yMaxDomain}}>
            <VictoryLine data={data} interpolation='monotoneX' />
            <VictoryScatter data={data} labels={yLabels} size={4} />
            <VictoryAxis tickValues={xTickValues} />
            <VictoryAxis dependentAxis tickValues={yTickValues} axisComponent={<></>} tickLabelComponent={<></>} />
        </VictoryChart>
    );
};
