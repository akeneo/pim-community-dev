import {Action} from 'redux';
import {AssetCollectionState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/asset-collection';
import {sameValue, Value as CommonValue} from 'akeneopimenrichmentassetmanager/enrich/domain/model/product';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';

export type AssetCollectionData = AssetCode[];

export type ProductIdentifier = string;
export type ValueCollection = Value[];
export type LabelCollection = {
  [locale: string]: string;
};
export type ProductState = {
  identifier: ProductIdentifier | null;
  values: ValueCollection;
  labels: LabelCollection;
};

export type Value = CommonValue & {
  data: AssetCollectionData;
};
export const productReducer = (
  state: ProductState = {identifier: null, values: [], labels: {}},
  action: ValuesUpdatedAction | ValueChangedAction | LabelsUpdatedAction | ProductIdentifierChangedAction
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
        values: [...state.values.map((value: Value) => (sameValue(value, action.value) ? action.value : value))],
      };
      break;
    case 'PRODUCT_IDENTIFIER_CHANGED':
      state = {
        ...state,
        identifier: action.payload,
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

type ProductIdentifierChangedAction = Action<'PRODUCT_IDENTIFIER_CHANGED'> & {payload: ProductIdentifier};
export const productIdentifierChanged = (payload: ProductIdentifier) => {
  return {type: 'PRODUCT_IDENTIFIER_CHANGED', payload};
};

export const selectProductLabels = (state: AssetCollectionState) => {
  return state.product.labels;
};

export const selectCurrentValues = (state: AssetCollectionState) => {
  const currentLocale = state.context.locale;
  const currentChannel = state.context.channel;

  return state.product.values.filter(
    ({locale, channel, attribute}: Value) =>
      (0 === attribute.availableLocales.length || attribute.availableLocales.includes(currentLocale)) &&
      (locale === null || locale === currentLocale) &&
      (channel === null || currentChannel === channel)
  );
};

export const selectProductIdentifer = (state: AssetCollectionState): ProductIdentifier | null => {
  return state.product.identifier;
};

export const updateValueData = (value: Value, data: AssetCode[]): Value => {
  return {
    ...value,
    data,
  };
};
