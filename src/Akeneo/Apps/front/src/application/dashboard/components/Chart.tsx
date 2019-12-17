import React from 'react';
import {VictoryAxis, VictoryChart, VictoryLine, VictoryScatter, VictoryStyleObject} from 'victory';

interface VictoryStyle {
    [property: string]: VictoryStyleObject;
}

const purple = '#52267d';
const lightPurple = '#ded5e4';
const grey = '#67768a';
const lightGrey = '#e8ebee';
const lightGreyStroke: React.CSSProperties = {
    strokeWidth: 1,
    stroke: lightGrey,
};

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
    axis: lightGreyStroke,
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
const scatterTheme: VictoryStyle = {
    labels: {
        fontSize: 13,
        fontWeight: 'bold',
        fill: ({index}: any) => (0 === index ? 'none' : purple),
        padding: 10,
    },
    data: {
        fill: purple,
    },
};
const lineTheme: VictoryStyle = {
    data: {
        stroke: lightPurple,
    },
};

type ChartData = {x: number; y: number; xLabel: string; yLabel: string};

export const Chart = ({data}: {data: ChartData[]}) => {
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

    const yLabels = ({datum}: any) => datum.yLabel;

    // The rendering order is based on the elements order. Axes must be first to be draw in background.
    return (
        <VictoryChart height={347} width={1000} domain={{x: [0.5, 7.5], y: [yMinDomain, yMaxDomain]}}>
            <VictoryAxis offsetY={50} tickValues={xDaysValues} style={daysAxeTheme} />
            <VictoryAxis dependentAxis tickValues={yGridAxes} style={yAxeTheme} />
            {[1.5, 2.5, 3.5, 4.5, 5.5, 6.5, 7.5].map((value, index) => {
                return <VictoryAxis dependentAxis key={index} style={gridYAxesTheme} axisValue={value} />;
            })}
            <VictoryLine data={data} interpolation='monotoneX' style={lineTheme} />
            <VictoryScatter data={data} labels={yLabels} size={7} style={scatterTheme} />
        </VictoryChart>
    );
};
