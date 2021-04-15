import {Action} from 'redux';
import {AssetCollectionState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/asset-collection';
import {Attribute} from 'akeneoassetmanager/platform/model/structure/attribute';
import fetchAllChannels from 'akeneoassetmanager/infrastructure/fetcher/channel';
import {Family, FamilyCode} from 'akeneoassetmanager/platform/model/structure/family';
import {
  familyFetcher,
  fetchFamily,
} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/family';
import {fetchRuleRelations} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/rule-relation';
import {RulesNumberByAttribute} from 'akeneoassetmanager/platform/model/structure/rule-relation';
import Locale from 'akeneoassetmanager/domain/model/locale';
import Channel from 'akeneoassetmanager/domain/model/channel';
import {AttributeGroupCollection} from 'akeneoassetmanager/platform/model/structure/attribute-group';
import {
  attributeGroupFetcher,
  fetchAssetAttributeGroups,
} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/attribute-group';

export type StructureState = {
  attributes: Attribute[];
  attributeGroups: AttributeGroupCollection;
  channels: Channel[];
  family: Family | null;
  rulesNumberByAttribute: {};
};

// Reducer
export const structureReducer = (
  state: StructureState = {attributes: [], attributeGroups: {}, channels: [], family: null, rulesNumberByAttribute: {}},
  action:
    | AttributeListUpdatedAction
    | AttributeGroupListUpdatedAction
    | ChannelListUpdatedAction
    | FamilyUpdatedAction
    | RuleRelationListUpdatedAction
) => {
  switch (action.type) {
    case 'ATTRIBUTE_LIST_UPDATED':
      state = {...state, attributes: action.attributes};
      break;
    case 'ATTRIBUTE_GROUP_LIST_UPDATED':
      state = {...state, attributeGroups: action.attributeGroups};
      break;
    case 'CHANNEL_LIST_UPDATED':
      state = {...state, channels: action.channels};
      break;
    case 'FAMILY_UPDATED':
      state = {...state, family: action.family};
      break;
    case 'RULE_RELATION_LIST_UPDATED':
      state = {...state, rulesNumberByAttribute: action.rulesNumberByAttribute};
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

type AttributeGroupListUpdatedAction = Action<'ATTRIBUTE_GROUP_LIST_UPDATED'> & {
  attributeGroups: AttributeGroupCollection;
};
export const attributeGroupListUpdated = (
  attributeGroups: AttributeGroupCollection
): AttributeGroupListUpdatedAction => {
  return {type: 'ATTRIBUTE_GROUP_LIST_UPDATED', attributeGroups};
};

type ChannelListUpdatedAction = Action<'CHANNEL_LIST_UPDATED'> & {channels: Channel[]};
export const channelListUpdated = (channels: Channel[]): ChannelListUpdatedAction => {
  return {type: 'CHANNEL_LIST_UPDATED', channels};
};

type FamilyUpdatedAction = Action<'FAMILY_UPDATED'> & {family: Family};
export const familyUpdated = (family: Family): FamilyUpdatedAction => {
  return {type: 'FAMILY_UPDATED', family};
};

type RuleRelationListUpdatedAction = Action<'RULE_RELATION_LIST_UPDATED'> & {
  rulesNumberByAttribute: RulesNumberByAttribute;
};
export const ruleRelationListUpdated = (
  rulesNumberByAttribute: RulesNumberByAttribute
): RuleRelationListUpdatedAction => {
  return {type: 'RULE_RELATION_LIST_UPDATED', rulesNumberByAttribute};
};

// Selectors
export const selectAttributeList = (state: AssetCollectionState): Attribute[] => {
  return state.structure.attributes;
};

export const selectAttributeGroupList = (state: AssetCollectionState): AttributeGroupCollection => {
  return state.structure.attributeGroups;
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

export const selectRuleRelations = (state: AssetCollectionState): RulesNumberByAttribute => {
  return state.structure.rulesNumberByAttribute;
};

export const updateAttributeGroups = () => async (dispatch: any) => {
  const attributeGroups = await fetchAssetAttributeGroups(attributeGroupFetcher())();
  dispatch(attributeGroupListUpdated(attributeGroups));
};

export const updateChannels = () => async (dispatch: any) => {
  const channels = await fetchAllChannels();
  dispatch(channelListUpdated(channels));
};

export const updateFamily = (familyCode: FamilyCode) => async (dispatch: any) => {
  const family = await fetchFamily(familyFetcher())(familyCode);
  dispatch(familyUpdated(family));
};

export const updateRuleRelations = (attributeCodes: string[]) => async (dispatch: any) => {
  const rulesNumberByAttribute = await fetchRuleRelations(attributeCodes);
  dispatch(ruleRelationListUpdated(rulesNumberByAttribute));
};
