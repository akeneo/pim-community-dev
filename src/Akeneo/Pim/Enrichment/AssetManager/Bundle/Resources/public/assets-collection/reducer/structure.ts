import {Action} from 'redux';
import {AssetCollectionState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/asset-collection';
import {Channel} from 'akeneopimenrichmentassetmanager/platform/model/channel/channel';
import {Attribute} from 'akeneopimenrichmentassetmanager/platform/model/structure/attribute';
import {Locale} from 'akeneopimenrichmentassetmanager/platform/model/channel/locale';
import {fetchChannels} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/channel';

export type StructureState = {
  attributes: Attribute[];
  channels: Channel[];
};

// Reducer
export const structureReducer = (
  state: StructureState = {attributes: [], channels: []},
  action: AttributeListUpdatedAction | ChannelListUpdatedAction
) => {
  switch (action.type) {
    case 'ATTRIBUTE_LIST_UPDATED':
      state = {...state, attributes: action.attributes};
      break;
    case 'CHANNEL_LIST_UPDATED':
      state = {...state, channels: action.channels};
      break;
    default:
      break;
  }

  return state;
};

// Action creators
type AttributeListUpdatedAction = Action<'ATTRIBUTE_LIST_UPDATED'> & {attributes: Attribute[]};
export const attributeListUpdated = (attributes: Attribute[]) => {
  return {type: 'ATTRIBUTE_LIST_UPDATED', attributes};
};

type ChannelListUpdatedAction = Action<'CHANNEL_LIST_UPDATED'> & {channels: Channel[]};
export const channelListUpdated = (channels: Channel[]) => {
  return {type: 'CHANNEL_LIST_UPDATED', channels};
};

// Selectors
export const selectAttributeList = (state: AssetCollectionState): Attribute[] => {
  return state.structure.attributes;
};

export const selectChannels = (state: AssetCollectionState): Channel[] => {
  return state.structure.channels;
};

export const selectLocales = (state: AssetCollectionState): Locale[] => {
  const locales = state.structure.channels.reduce((locales: Locale[], channel: Channel) => {
    return [...locales, ...channel.locales];
  }, []);

  return locales.reduce((locales: Locale[], locale: Locale) => {
    const isLocaleAlreadyInArray = locales.some(
      (alreadyInArrayLocale: Locale) => alreadyInArrayLocale.code === locale.code
    );
    if (isLocaleAlreadyInArray) return locales;

    return [...locales, locale];
  }, []);
};

export const updateChannels = () => async (dispatch: any) => {
  const channels = await fetchChannels();
  dispatch(channelListUpdated(channels));
};
