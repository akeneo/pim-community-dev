import {Theme} from '../theme';
import {color, fontSize} from '../common';

const pimTheme: Theme = {
  color,
  fontSize,
  palette: {
    primary: 'green',
    secondary: 'blue',
    tertiary: 'grey',
    warning: 'yellow',
    danger: 'red',
    brand: color.purple100,
  },
};

export {pimTheme};
