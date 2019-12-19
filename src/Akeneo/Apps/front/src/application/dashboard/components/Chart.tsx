import React from 'react';
import {
    VictoryAxis,
    VictoryChart,
    VictoryLine,
    VictoryScatter,
    VictoryStyleObject,
    VictoryThemeDefinition,
} from 'victory';
import {lightGreyStroke, purple, grey, darkGrey, lightGrey} from '../../apps/EventChartThemes';

interface VictoryStyle {
    [property: string]: VictoryStyleObject;
}

const yAxeTheme: VictoryStyle = {
    tickLabels: {
        fill: 'none',
    },
    axis: lightGreyStroke,
    grid: lightGreyStroke,
};
const daysAxeTheme: VictoryStyle = {
    tickLabels: {
        fontSize: 11,
        fontFamily: 'Lato',
        fontWeight: ({tickValue}) => (7 === tickValue ? 'bold' : 'normal'),
        fill: ({tickValue}) => (7 === tickValue ? purple : grey),
    },
    axis: {
        stroke: 'none',
    },
    grid: {
        stroke: 'none',
    },
};
const gridYAxesTheme: VictoryStyle = {
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
    const yMax = data.reduce((maxY, {y}) => (y > maxY ? y : maxY), 0);
    const yMin = data.reduce((minY, {y}) => (y < minY ? y : minY), yMax);
    const step = (yMax - yMin) / 5;

    // max and min domain focus the graph on the data.
    const yMaxDomain = Math.round(yMax + step);
    const yMinDomain = Math.round(yMin - step);

    // The first x value need to be hidden. It is used to see the hidden start of the graph.
    const xDaysValues = data.map(({xLabel}) => xLabel).slice(1);

    const firstYGridAxe = yMin - step;
    // We draw an Y axe each 20% of he graph.
    const yGridAxes = Array.from(new Array(7)).map((_, index) => firstYGridAxe + step * index);

    const yLabels = ({datum}: {datum: ChartData}) => datum.yLabel;

    // The rendering order is based on the elements order. Axes must be first to be draw in background.
    return (
        <VictoryChart
            height={300}
            width={1000}
            padding={0}
            domain={{x: [0.5, 7.5], y: [yMinDomain, yMaxDomain]}}
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
