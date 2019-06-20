import {NormalizedAsset} from 'akeneoassetmanager/domain/model/asset/asset';
import formState, {FormState} from 'akeneoassetmanager/application/reducer/state';
import ValidationError from 'akeneoassetmanager/domain/model/validation-error';
import {combineReducers} from 'redux';
import {NormalizedFile} from 'akeneoassetmanager/domain/model/file';
import {NormalizedValue} from 'akeneoassetmanager/domain/model/asset/value';

export interface EditionFormState {
  state: FormState;
  data: NormalizedAsset;
  errors: ValidationError[];
}

const stateReducer = formState('asset', 'ASSET_EDITION_UPDATED', 'ASSET_EDITION_RECEIVED');

const dataReducer = (
  state: NormalizedAsset = {
    identifier: '',
    asset_family_identifier: '',
    code: '',
    labels: {},
    image: null,
    values: [],
  },
  {
    type,
    asset,
    label,
    locale,
    image,
    value,
  }: {
    type: string;
    asset: NormalizedAsset;
    label: string;
    locale: string;
    image: NormalizedFile;
    value: NormalizedValue;
  }
) => {
  switch (type) {
    case 'ASSET_EDITION_RECEIVED':
      state = asset;
      break;
    case 'ASSET_EDITION_LABEL_UPDATED':
      state = {...state, labels: {...state.labels, [locale]: label}};
      break;
    case 'ASSET_EDITION_IMAGE_UPDATED':
      state = {...state, image};
      break;
    case 'ASSET_EDITION_VALUE_UPDATED':
      state = {
        ...state,
        values: state.values.map((currentValue: NormalizedValue) => {
          if (
            currentValue.channel === value.channel &&
            currentValue.locale === value.locale &&
            currentValue.attribute.identifier === value.attribute.identifier &&
            currentValue.data !== value.data
          ) {
            return value;
          }

          return currentValue;
        }),
      };
      break;
    default:
      break;
  }

  return state;
};

const errorsReducer = (state: ValidationError[] = [], action: {type: string; errors: ValidationError[]}) => {
  switch (action.type) {
    case 'ASSET_EDITION_SUBMISSION':
      state = [];
      break;
    case 'ASSET_EDITION_ERROR_OCCURED':
      state = action.errors;
      break;
    default:
      break;
  }

  return state;
};

export default combineReducers({
  state: stateReducer,
  data: dataReducer,
  errors: errorsReducer,
});
