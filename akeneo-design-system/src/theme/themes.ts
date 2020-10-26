import {pimTheme} from './pim';
import {onboarderTheme} from './onboarder';
import {sharedCatalogsTheme} from './shared-catalogs';

const indexedThemes = {
  PIM: pimTheme,
  Onboarder: onboarderTheme,
  'Shared Catalogs': sharedCatalogsTheme,
};

const themes = [pimTheme, onboarderTheme, sharedCatalogsTheme];

export {themes, indexedThemes};
