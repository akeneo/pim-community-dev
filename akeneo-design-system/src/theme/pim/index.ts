import {Theme} from '../theme';
import {color, fontSize, palette} from '../common';

const pimTheme: Theme = {
  name: 'PIM',
  color: {
    ...color,
    brand20: '#eadcf1',
    brand40: '#d4bae3',
    brand60: '#be97d5',
    brand80: '#a974c7',
    brand100: '#9452ba',
    brand120: '#764194',
    brand140: '#58316f',
  },
  fontSize,
  palette,
};

export {pimTheme};
