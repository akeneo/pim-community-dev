import {Theme} from '../theme';
import {color, fontSize, palette, scoringPalette} from '../common';

const onboarderTheme: Theme = {
  name: 'Onboarder',
  color: {
    ...color,
    brand20: '#dbedf8',
    brand40: '#b7dcf2',
    brand60: '#93caec',
    brand80: '#6fb9e6',
    brand100: '#4ca8e0',
    brand120: '#3c86b3',
    brand140: '#2d6486',
  },
  fontSize,
  palette,
  scoringPalette,
};

export {onboarderTheme};
