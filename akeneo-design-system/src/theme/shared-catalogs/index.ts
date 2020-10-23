import {Theme} from '../theme';
import {color, fontSize, palette} from '../common';

const sharedCatalogsTheme: Theme = {
  color: {
    ...color,
    brand20: '#FDF0D8',
    brand40: '#FCE1B2',
    brand60: '#FBD28B',
    brand80: '#FAC365',
    brand100: '#F9B53F',
    brand120: '#C79032',
    brand140: '#956C25',
  },
  fontSize,
  palette,
};

export {sharedCatalogsTheme};
