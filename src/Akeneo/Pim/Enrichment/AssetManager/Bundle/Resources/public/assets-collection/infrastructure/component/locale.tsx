import {connect} from 'react-redux';
import {LocaleLabel as PlatformLocaleLabel} from 'akeneopimenrichmentassetmanager/platform/component/channel/locale';
import {AssetCollectionState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/asset-collection';
import {selectCurrentLocale} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/context';
import {selectLocales} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/structure';

export const LocaleLabel = connect((state: AssetCollectionState) => ({
  locales: selectLocales(state),
  locale: selectCurrentLocale(state)
}))(PlatformLocaleLabel)
