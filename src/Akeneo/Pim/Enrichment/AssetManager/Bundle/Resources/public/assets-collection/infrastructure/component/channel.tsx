import {connect} from 'react-redux';
import {ChannelLabel as PlatformChannelLabel} from 'akeneopimenrichmentassetmanager/platform/component/channel/channel';
import {AssetCollectionState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/asset-collection';
import {selectCurrentLocale} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/context';
import {selectChannels} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/structure';

export const ChannelLabel = connect((state: AssetCollectionState) => ({
  channels: selectChannels(state),
  locale: selectCurrentLocale(state)
}))(PlatformChannelLabel)
