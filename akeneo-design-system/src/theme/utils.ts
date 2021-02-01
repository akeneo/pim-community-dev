import {AkeneoThemedProps, FontSize, Level} from 'theme';

const getFontWeight = (weight: 'regular' | 'semibold' | 'bold') => {
  switch (weight) {
    case 'regular':
      return 400;
    case 'semibold':
      return 600;
    case 'bold':
      return 700;
  }
};

const getColor = (color: string, gradient?: number): ((props: AkeneoThemedProps) => string) => ({
  theme,
}: AkeneoThemedProps): string => theme.color[`${color}${gradient ?? ''}`] as string;

const getColorForLevel = (level: Level, gradient: number): ((props: AkeneoThemedProps) => string) => ({
  theme,
}: AkeneoThemedProps): string => theme.color[`${theme.palette[level]}${gradient}`] as string;

const getFontSize = (fontSize: keyof FontSize): ((props: AkeneoThemedProps) => string) => ({
  theme,
}: AkeneoThemedProps): string => theme.fontSize[fontSize];

export {getColor, getColorForLevel, getFontSize, getFontWeight};
