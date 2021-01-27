import {Theme} from '../theme';
import {color, fontSize, palette, scoringPalette} from '../common';

const sharedCatalogsTheme: Theme = {
  name: 'Shared Catalogs',
  color: {
    ...color,
    brand20: '#fdf0d8',
    brand40: '#fce1b2',
    brand60: '#fbd28b',
    brand80: '#fac365',
    brand100: '#f9b53f',
    brand120: '#c79032',
    brand140: '#956c25',
  },
  fontSize,
  palette,
  scoringPalette,
};

export {sharedCatalogsTheme};
