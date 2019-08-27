import {Action} from 'redux';
import {AssetCollectionState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/asset-collection';
import {Channel} from 'akeneopimenrichmentassetmanager/platform/model/channel/channel';
import {Attribute} from 'akeneopimenrichmentassetmanager/platform/model/structure/attribute';
import {Locale} from 'akeneopimenrichmentassetmanager/platform/model/channel/locale';
import {fetchChannels} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/channel';
import {Family, FamilyCode} from 'akeneopimenrichmentassetmanager/platform/model/structure/family';
import {fetchFamily} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/family';

export type StructureState = {
  attributes: Attribute[];
  channels: Channel[];
  family: Family | null;
  //ruleRelations: RuleRelation[]
};

// Reducer
export const structureReducer = (
  state: StructureState = {attributes: [], channels: [], family: null},
  action: AttributeListUpdatedAction | ChannelListUpdatedAction | FamilyUpdatedAction
) => {
  switch (action.type) {
    case 'ATTRIBUTE_LIST_UPDATED':
      state = {...state, attributes: action.attributes};
      break;
    case 'CHANNEL_LIST_UPDATED':
      state = {...state, channels: action.channels};
      break;
    case 'FAMILY_UPDATED':
      state = {...state, family: action.family};
      break;
    default:
      break;
  }

  return state;
};

// Action creators
type AttributeListUpdatedAction = Action<'ATTRIBUTE_LIST_UPDATED'> & {attributes: Attribute[]};
export const attributeListUpdated = (attributes: Attribute[]): AttributeListUpdatedAction => {
  return {type: 'ATTRIBUTE_LIST_UPDATED', attributes};
};

type ChannelListUpdatedAction = Action<'CHANNEL_LIST_UPDATED'> & {channels: Channel[]};
export const channelListUpdated = (channels: Channel[]): ChannelListUpdatedAction => {
  return {type: 'CHANNEL_LIST_UPDATED', channels};
};

type FamilyUpdatedAction = Action<'FAMILY_UPDATED'> & {family: Family};
export const familyUpdated = (family: Family): FamilyUpdatedAction => {
  return {type: 'FAMILY_UPDATED', family};
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

export const selectFamily = (state: AssetCollectionState) => {
  return state.structure.family;
};

export const updateChannels = () => async (dispatch: any) => {
  const channels = await fetchChannels();
  dispatch(channelListUpdated(channels));
};

export const updateFamily = (familyCode: FamilyCode) => async (dispatch: any) => {
  const family = await fetchFamily(familyCode);
  dispatch(familyUpdated(family));
};
