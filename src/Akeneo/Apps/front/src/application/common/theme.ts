import {ThemedStyledProps} from 'styled-components';

export const theme = {
    color: {
        blue: '#5992c7',
        darkBlue: '#11324d',
        grey: '#a1a9b7',
        slateGrey: '#67768a',
    },
};

export type PropsWithTheme<P = {}> = ThemedStyledProps<P, typeof theme>;
