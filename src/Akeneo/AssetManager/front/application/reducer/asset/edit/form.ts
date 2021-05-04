import formState, {FormState} from 'akeneoassetmanager/application/reducer/state';
import {ValidationError} from '@akeneo-pim-community/shared';
import {combineReducers} from 'redux';
import EditionValue, {areValuesEqual} from 'akeneoassetmanager/domain/model/asset/edition-value';
import EditionAsset, {createEmptyEditionAsset} from 'akeneoassetmanager/domain/model/asset/edition-asset';

export interface EditionFormState {
  state: FormState;
  data: EditionAsset;
  errors: ValidationError[];
}

const stateReducer = formState('asset', 'ASSET_EDITION_UPDATED', 'ASSET_EDITION_RECEIVED');

const dataReducer = (
  state: EditionAsset = createEmptyEditionAsset(),
  {
    type,
    asset,
    value,
  }: {
    type: string;
    asset: EditionAsset;
    label: string;
    locale: string;
    image: [];
    value: EditionValue;
  }
) => {
  switch (type) {
    case 'ASSET_EDITION_RECEIVED':
      state = asset;
      break;
    case 'ASSET_EDITION_VALUE_UPDATED':
      state = {
        ...state,
        values: state.values.map((currentValue: EditionValue) => {
          if (areValuesEqual(currentValue, value) && currentValue.data !== value.data) {
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
