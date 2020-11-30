import {pimTheme, AkeneoThemedProps} from 'akeneo-design-system';

const opacity = (color: string, opacity: number) => {
  const transparencyHexValue = Math.round(opacity * 255).toString(16);

  return `${color}${transparencyHexValue.length < 2 ? '0' : ''}${transparencyHexValue}`;
};

export {opacity, pimTheme as akeneoTheme, AkeneoThemedProps as ThemedProps};
