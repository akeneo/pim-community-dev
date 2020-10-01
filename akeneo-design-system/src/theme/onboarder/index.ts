import {Theme} from '../theme';
import {color, fontSize} from '../common';

const onboarderTheme: Theme = {
  color,
  fontSize,
  palette: {
    primary: 'green',
    secondary: 'blue',
    tertiary: 'grey',
    warning: 'yellow',
    danger: 'red',
    logo: color.blue100,
  },
};

export {onboarderTheme};
