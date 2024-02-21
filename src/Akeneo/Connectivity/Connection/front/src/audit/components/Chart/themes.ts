import {VictoryThemeDefinition} from 'victory';

const blue = '#3b438c';
const green = '#3d7170';
const grey = '#67768a';
const lightBlue = '#dee0ef';
const lightGreen = '#bbe6e6';
const lightGrey = '#e8ebee';
const lightPurple = '#ded5e4';
const lightRed = '#f7dee3';
const purple = '#52267d';
const red = '#c92343';

const theme = (primaryColor: string, secondaryColor: string): VictoryThemeDefinition => ({
    line: {
        style: {
            data: {
                stroke: secondaryColor,
            },
        },
        width: 0,
        height: 0,
        colorScale: [],
    },
    scatter: {
        style: {
            labels: {
                fontSize: 13,
                fontWeight: 'normal',
                fill: primaryColor,
                padding: 10,
            },
            data: {
                fill: primaryColor,
            },
        },
        width: 0,
        height: 0,
        colorScale: [],
    },
});

export const themes = {
    blue: theme(blue, lightBlue),
    green: theme(green, lightGreen),
    purple: theme(purple, lightPurple),
    red: theme(red, lightRed),
};

export const yAxeTheme = {
    tickLabels: {
        fill: 'none',
    },
    axis: {
        strokeWidth: 1,
        stroke: lightGrey,
    },
    grid: {
        strokeWidth: 1,
        stroke: lightGrey,
    },
};
export const daysAxeTheme = {
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
export const gridYAxesTheme = {
    tickLabels: {
        fill: 'none',
    },
    grid: {
        stroke: 'none',
    },
    axis: {
        strokeWidth: 1,
        stroke: lightGrey,
    },
};
