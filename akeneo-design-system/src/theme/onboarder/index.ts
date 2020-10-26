import {Theme} from '../theme';
import {color, fontSize, palette} from '../common';

const onboarderTheme: Theme = {
  name: 'Onboarder',
  color: {
    ...color,
    brand20: '#DBEDF8',
    brand40: '#B7DCF2',
    brand60: '#93CAEC',
    brand80: '#6FB9E6',
    brand100: '#4CA8E0',
    brand120: '#3C86B3',
    brand140: '#2D6486',
  },
  fontSize,
  palette,
};

export {onboarderTheme};
