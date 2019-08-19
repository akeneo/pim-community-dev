import {Action} from 'redux';
import {AttributeCode, Attribute} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/structure';
import {LocaleCode, ChannelCode} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/context';
import {AssetCollectionState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/asset-collection';

export type Meta = {
  attributes_for_this_level: AttributeCode[];
  level: number | null;
};
export type LocaleReference = LocaleCode | null;
export type ChannelReference = ChannelCode | null;

export type CategoryCode = string;

export type AssetCode = string;
export type AssetCollectionData = AssetCode[];
export type LegacyValue = {
  data: any;
  scope: ChannelReference;
  locale: LocaleReference;
  attribute: AttributeCode;
};
export type LegacyValueCollection = {
  [key: string]: LegacyValue[];
};

export type Product = {
  meta: Meta;
  values: LegacyValueCollection;
  categories: CategoryCode[];
};

export type Value = {
  attribute: Attribute;
  locale: LocaleReference;
  channel: ChannelReference;
  data: AssetCollectionData;
  editable: boolean;
};

export type ValueCollection = Value[];
export type ValuesState = ValueCollection;

export const valuesReducer = (state: ValuesState = [], action: ValuesUpdatedAction) => {
  switch (action.type) {
    case 'VALUE_COLLECTION_UPDATED':
      state = action.values;
      break;
    default:
      break;
  }

  return state;
};

type ValuesUpdatedAction = Action<'VALUE_COLLECTION_UPDATED'> & {values: ValueCollection};
export const valuesUpdated = (values: Value[]) => {
  return {type: 'VALUE_COLLECTION_UPDATED', values};
};

export const selectCurrentValues = (state: AssetCollectionState) => {
  const currentLocale = state.context.locale;
  const currentChannel = state.context.channel;

  return state.values.filter(
    ({locale, channel}: Value) =>
      (locale === null || locale === currentLocale) && (channel === null || currentChannel === channel)
  );
};
