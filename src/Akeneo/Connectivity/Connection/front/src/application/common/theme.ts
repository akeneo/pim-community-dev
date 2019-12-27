import {ThemedStyledProps} from 'styled-components';

export const theme = {
    color: {
        blue10: '#f5f9fc',
        blue100: '#5992c7',
        grey60: '#f9f9fb',
        grey80: '#d9dde2',
        grey100: '#a1a9b7',
        grey120: '#67768a',
        grey140: '#11324d',
        purple100: '#9452ba',
    },
    fontSize: {
        title: '30px',
        bigger: '17px',
        big: '15px',
        default: '13px',
        small: '11px',
    },
};

export type PropsWithTheme<P = {}> = ThemedStyledProps<P, typeof theme>;
