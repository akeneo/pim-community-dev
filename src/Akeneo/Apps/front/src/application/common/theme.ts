import {ThemedStyledProps} from 'styled-components';

export const theme = {
    color: {
        blue: '#5992c7',
        darkBlue: '#11324d',
        grey: '#a1a9b7',
        mediumGrey: '#d9dde2',
        purple: '#9452ba',
        slateGrey: '#67768a',
        blue10: '#f5f9fc',
    },
};

export type PropsWithTheme<P = {}> = ThemedStyledProps<P, typeof theme>;
