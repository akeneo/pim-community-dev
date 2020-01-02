import {VictoryThemeDefinition} from 'victory';
import {CSSProperties} from 'react';

export const blue = '#3b438c';
export const darkGrey = '#11324d';
export const grey = '#67768a';
export const lightBlue = '#dee0ef';
export const lightGrey = '#e8ebee';
export const lightPurple = '#ded5e4';
export const purple = '#52267d';

export const lightGreyStroke: CSSProperties = {
    strokeWidth: 1,
    stroke: lightGrey,
};

const themeBaseProps: {width: number; height: number; colorScale: string[]} = {
    width: 1000,
    height: 300,
    colorScale: [],
};

export const purpleTheme: VictoryThemeDefinition = {
    line: {
        style: {
            data: {
                stroke: lightPurple,
            },
        },
        ...themeBaseProps,
    },
    scatter: {
        style: {
            labels: {
                fontSize: 13,
                fontWeight: 'normal',
                fill: purple,
                padding: 10,
            },
            data: {
                fill: purple,
            },
        },
        ...themeBaseProps,
    },
};

export const blueTheme: VictoryThemeDefinition = {
    line: {
        style: {
            data: {
                stroke: lightBlue,
            },
        },
        ...themeBaseProps,
    },
    scatter: {
        style: {
            labels: {
                fontSize: 13,
                fontWeight: 'normal',
                fill: blue,
                padding: 10,
            },
            data: {
                fill: blue,
            },
        },
        ...themeBaseProps,
    },
};
