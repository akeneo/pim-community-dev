import {ThemedStyledProps} from 'styled-components';

type FontSize = {
  big: string;
  bigger: string;
  default: string;
  small: string;
  title: string;
};

type Color = {
  blue10: string;
  blue100: string;
  blue120: string;
  blue140: string;
  blue20: string;
  blue40: string;
  blue60: string;
  blue80: string;
  green100: string;
  green120: string;
  green140: string;
  green20: string;
  green40: string;
  green60: string;
  green80: string;
  grey100: string;
  grey120: string;
  grey140: string;
  grey20: string;
  grey40: string;
  grey60: string;
  grey80: string;
  purple100: string;
  purple120: string;
  purple140: string;
  purple20: string;
  purple40: string;
  purple60: string;
  purple80: string;
  red100: string;
  red120: string;
  red140: string;
  red20: string;
  red40: string;
  red60: string;
  red80: string;
  white: string;
  yellow100: string;
  yellow120: string;
  yellow140: string;
  yellow20: string;
  yellow40: string;
  yellow60: string;
  yellow80: string;
};

type Theme = {
  fontSize: FontSize;
  color: Color;
};

const getColor = (color: string): ((props: AkeneoThemedProps) => string) => ({theme}: AkeneoThemedProps): string => {
  return theme.color[color] as string;
};
const getFontSize = (fontSize: string): ((props: AkeneoThemedProps) => string) => ({
  theme,
}: AkeneoThemedProps): string => {
  return theme.fontSize[fontSize] as string;
};

export type AkeneoThemedProps<P = {[key: string]: any}> = ThemedStyledProps<P, Theme>;
export type {Theme, FontSize, Color};
export {getColor, getFontSize};
