import {Action} from 'redux';
import {AssetCollectionState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/asset-collection';
import {Value as CommonValue, sameValue} from 'akeneopimenrichmentassetmanager/enrich/domain/model/product';

export type AssetCode = string;
export type AssetCollectionData = AssetCode[];

export type ValueCollection = Value[];
export type LabelCollection = {
  [locale: string]: string;
};
export type ProductState = {
  values: ValueCollection;
  labels: LabelCollection;
};

export type Value = CommonValue & {
  data: AssetCollectionData;
};
export const productReducer = (
  state: ProductState = {values: [], labels: {}},
  action: ValuesUpdatedAction | ValueChangedAction | LabelsUpdatedAction
) => {
  switch (action.type) {
    case 'VALUE_COLLECTION_UPDATED':
      state = {...state, values: action.values};
      break;
    case 'LABEL_COLLECTION_UPDATED':
      state = {...state, labels: action.labels};
      break;
    case 'VALUE_CHANGED':
      state = {
        ...state,
        values: [...state.values.filter((value: Value) => !sameValue(value, action.value)), action.value],
      };
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

type LabelsUpdatedAction = Action<'LABEL_COLLECTION_UPDATED'> & {labels: LabelCollection};
export const labelsUpdated = (labels: Value[]) => {
  return {type: 'LABEL_COLLECTION_UPDATED', labels};
};

type ValueChangedAction = Action<'VALUE_CHANGED'> & {value: Value};
export const valueChanged = (value: Value) => {
  return {type: 'VALUE_CHANGED', value};
};

export const selectProductLabels = (state: AssetCollectionState) => {
  return state.product.labels;
};

export const selectCurrentValues = (state: AssetCollectionState) => {
  const currentLocale = state.context.locale;
  const currentChannel = state.context.channel;

  return state.product.values.filter(
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
