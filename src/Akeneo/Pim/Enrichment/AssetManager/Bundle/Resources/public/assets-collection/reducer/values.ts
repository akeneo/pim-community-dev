import {Action} from 'redux';
import {AssetCollectionState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/asset-collection';
import {Value as CommonValue, sameValue} from 'akeneopimenrichmentassetmanager/enrich/domain/model/product';

export type AssetCode = string;
export type AssetCollectionData = AssetCode[];

export type ValueCollection = Value[];
export type ValuesState = ValueCollection;

export type Value = CommonValue & {
  data: AssetCollectionData;
};
export const valuesReducer = (state: ValuesState = [], action: ValuesUpdatedAction | ValueChangedAction) => {
  switch (action.type) {
    case 'VALUE_COLLECTION_UPDATED':
      state = action.values;
      break;
    case 'VALUE_CHANGED':
      state = [...state.filter((value: Value) => !sameValue(value, action.value)), action.value];
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

type ValueChangedAction = Action<'VALUE_CHANGED'> & {value: Value};
export const valueChanged = (value: Value) => {
  return {type: 'VALUE_CHANGED', value};
};

export const selectCurrentValues = (state: AssetCollectionState) => {
  const currentLocale = state.context.locale;
  const currentChannel = state.context.channel;

  return state.values.filter(
    ({locale, channel}: Value) =>
      (locale === null || locale === currentLocale) && (channel === null || currentChannel === channel)
  );
};

export const updateValueData = (value: Value, data: AssetCode[]): Value => {
  return {
    ...value,
    data,
  };
};
