import {ChannelLabel as PlatformChannelLabel} from 'akeneoassetmanager/platform/component/channel/channel';
import {selectChannels} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/structure';
import {connect} from 'react-redux';
import {LocaleLabel as PlatformLocaleLabel} from 'akeneoassetmanager/platform/component/channel/locale';
import {AssetCollectionState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/asset-collection';
import {selectCurrentLocale} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/context';
import {selectLocales} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/structure';

export const LocaleLabel = connect((state: AssetCollectionState) => ({
  locales: selectLocales(state),
  locale: selectCurrentLocale(state),
}))(PlatformLocaleLabel);

export const ChannelLabel = connect((state: AssetCollectionState) => ({
  channels: selectChannels(state),
  locale: selectCurrentLocale(state),
}))(PlatformChannelLabel);
