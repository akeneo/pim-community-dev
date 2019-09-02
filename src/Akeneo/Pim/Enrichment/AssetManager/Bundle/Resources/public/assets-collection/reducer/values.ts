import {Action} from 'redux';
import {AssetCollectionState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/asset-collection';
import {Value as CommonValue} from 'akeneopimenrichmentassetmanager/enrich/domain/model/product';

export type AssetCode = string;
export type AssetCollectionData = AssetCode[];

export type ValueCollection = Value[];
export type ValuesState = ValueCollection;

export type Value = CommonValue & {
  data: AssetCollectionData;
};

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
