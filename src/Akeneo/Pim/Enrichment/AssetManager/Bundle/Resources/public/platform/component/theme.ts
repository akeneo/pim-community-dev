import {ThemedStyledProps} from 'styled-components';

type AkeneoTheme = {
  color: {
    grey60: string;
    grey80: string;
    grey100: string;
    grey120: string;
    grey140: string;
    purple100: string;
    yellow100: string;
  };
  fontSize: {
    bigger: string;
    big: string;
    default: string;
    small: string;
  };
};

export type ThemedProps<P> = ThemedStyledProps<P, AkeneoTheme>;

export const akeneoTheme: AkeneoTheme = {
  color: {
    grey60: '#f9f9fb',
    grey80: '#d9dde2',
    grey100: '#a1a9b7',
    grey120: '#67768a',
    grey140: '#11324d',
    purple100: '#9452ba',
    yellow100: '#f9b53f',
  },
  fontSize: {
    bigger: '17px',
    big: '15px',
    default: '13px',
    small: '11px',
  },
};
