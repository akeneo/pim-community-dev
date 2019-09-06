import {Action} from 'redux';
import {AssetCollectionState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/asset-collection';
import {Channel} from 'akeneopimenrichmentassetmanager/platform/model/channel/channel';
import {Attribute} from 'akeneopimenrichmentassetmanager/platform/model/structure/attribute';
import {Locale} from 'akeneopimenrichmentassetmanager/platform/model/channel/locale';
import {
  channelFetcher,
  fetchChannels,
} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/channel';
import {Family, FamilyCode} from 'akeneopimenrichmentassetmanager/platform/model/structure/family';
import {
  familyFetcher,
  fetchFamily,
} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/family';
import {fetchRuleRelations} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/rule-relation';
import {RuleRelation} from 'akeneopimenrichmentassetmanager/platform/model/structure/rule-relation';

export type StructureState = {
  attributes: Attribute[];
  channels: Channel[];
  family: Family | null;
  ruleRelations: RuleRelation[];
};

// Reducer
export const structureReducer = (
  state: StructureState = {attributes: [], channels: [], family: null, ruleRelations: []},
  action: AttributeListUpdatedAction | ChannelListUpdatedAction | FamilyUpdatedAction | RuleRelationListUpdatedAction
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
    case 'RULE_RELATION_LIST_UPDATED':
      state = {...state, ruleRelations: action.ruleRelations};
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

type RuleRelationListUpdatedAction = Action<'RULE_RELATION_LIST_UPDATED'> & {ruleRelations: RuleRelation[]};
export const ruleRelationListUpdated = (ruleRelations: RuleRelation[]): RuleRelationListUpdatedAction => {
  return {type: 'RULE_RELATION_LIST_UPDATED', ruleRelations};
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

export const selectFamily = (state: AssetCollectionState): Family | null => {
  return state.structure.family;
};

export const selectRuleRelations = (state: AssetCollectionState): RuleRelation[] => {
  return state.structure.ruleRelations;
};

export const updateChannels = () => async (dispatch: any) => {
  const channels = await fetchChannels(channelFetcher())();
  dispatch(channelListUpdated(channels));
};

export const updateFamily = (familyCode: FamilyCode) => async (dispatch: any) => {
  const family = await fetchFamily(familyFetcher())(familyCode);
  dispatch(familyUpdated(family));
};

export const updateRuleRelations = () => async (dispatch: any) => {
  const ruleRelations = await fetchRuleRelations();
  dispatch(ruleRelationListUpdated(ruleRelations));
};
