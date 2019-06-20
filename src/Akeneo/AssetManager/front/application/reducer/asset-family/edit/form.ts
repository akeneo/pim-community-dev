import {NormalizedAssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import formState, {FormState} from 'akeneoassetmanager/application/reducer/state';
import ValidationError from 'akeneoassetmanager/domain/model/validation-error';
import {combineReducers} from 'redux';
import {NormalizedFile} from 'akeneoassetmanager/domain/model/file';

export interface EditionFormState {
  state: FormState;
  data: NormalizedAssetFamily;
  errors: ValidationError[];
}

const stateReducer = formState('assetFamily', 'ASSET_FAMILY_EDITION_UPDATED', 'ASSET_FAMILY_EDITION_RECEIVED');

const dataReducer = (
  state: NormalizedAssetFamily = {
    identifier: '',
    code: '',
    labels: {},
    image: null,
    attribute_as_image: '',
    attribute_as_label: '',
  },
  {
    type,
    assetFamily,
    value,
    locale,
    image,
  }: {type: string; assetFamily: NormalizedAssetFamily; value: string; locale: string; image: NormalizedFile}
) => {
  switch (type) {
    case 'ASSET_FAMILY_EDITION_RECEIVED':
      state = assetFamily;
      break;
    case 'ASSET_FAMILY_EDITION_LABEL_UPDATED':
      state = {...state, labels: {...state.labels, [locale]: value}};
      break;
    case 'ASSET_FAMILY_EDITION_IMAGE_UPDATED':
      state = {...state, image};
      break;
    default:
      break;
  }

  return state;
};

const errorsReducer = (state: ValidationError[] = [], action: {type: string; errors: ValidationError[]}) => {
  switch (action.type) {
    case 'ASSET_FAMILY_EDITION_SUBMISSION':
      state = [];
      break;
    case 'ASSET_FAMILY_EDITION_ERROR_OCCURED':
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
