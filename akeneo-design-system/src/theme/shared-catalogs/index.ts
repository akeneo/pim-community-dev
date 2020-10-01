import {Theme} from '../theme';
import {color, fontSize} from '../common';

const sharedCatalogsTheme: Theme = {
  color,
  fontSize,
  palette: {
    primary: 'green',
    secondary: 'blue',
    tertiary: 'grey',
    warning: 'yellow',
    danger: 'red',
    logo: color.yellow100,
  },
};

export {sharedCatalogsTheme};
