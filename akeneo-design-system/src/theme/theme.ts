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
  green10: string;
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
  red10: string;
  red100: string;
  red120: string;
  red140: string;
  red20: string;
  red40: string;
  red60: string;
  red80: string;
  yellow10: string;
  yellow100: string;
  yellow120: string;
  yellow140: string;
  yellow20: string;
  yellow40: string;
  yellow60: string;
  yellow80: string;
  brand20: string;
  brand40: string;
  brand60: string;
  brand80: string;
  brand100: string;
  brand120: string;
  brand140: string;
  white: string;
};

type Palette = {
  primary: string;
  secondary: string;
  tertiary: string;
  warning: string;
  danger: string;
};

type ScoringPalette = {
  a: string;
  b: string;
  c: string;
  d: string;
  e: string;
};

type Theme = {
  name: string;
  palette: Palette;
  scoringPalette: ScoringPalette;
  fontSize: FontSize;
  color: Color;
};

type Level = 'primary' | 'secondary' | 'tertiary' | 'warning' | 'danger';

type Score = keyof ScoringPalette;

const getColor = (color: string, gradient?: number): ((props: AkeneoThemedProps) => string) => ({
  theme,
}: AkeneoThemedProps): string => theme.color[`${color}${gradient ?? ''}`] as string;

const getColorForLevel = (level: Level, gradient: number): ((props: AkeneoThemedProps) => string) => ({
  theme,
}: AkeneoThemedProps): string => theme.color[`${theme.palette[level]}${gradient}`] as string;

const getColorForScoring = (score: Score, gradient: number): ((props: AkeneoThemedProps) => string) => ({
  theme,
}: AkeneoThemedProps): string => theme.color[`${theme.scoringPalette[score]}${gradient}`] as string;

const getFontSize = (fontSize: keyof FontSize): ((props: AkeneoThemedProps) => string) => ({
  theme,
}: AkeneoThemedProps): string => theme.fontSize[fontSize];

const sanitizeScoring = (score: string | null): Score | 'n/a' | null => {
  if (typeof score === 'string' && ['a', 'b', 'c', 'd', 'e', 'n/a'].includes(score.toLowerCase())) {
    return score.toLowerCase() as Score | 'n/a';
  }

  return null;
};
export type AkeneoThemedProps<P = Record<string, unknown>> = ThemedStyledProps<P, Theme>;
export type {Theme, FontSize, Color, Level, Score, Palette, ScoringPalette};
export {getColor, getColorForLevel, getColorForScoring, getFontSize, sanitizeScoring};
