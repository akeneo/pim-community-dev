import {AssetFamily, createEmptyAssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import formState, {FormState} from 'akeneoassetmanager/application/reducer/state';
import ValidationError from 'akeneoassetmanager/domain/model/validation-error';
import {combineReducers} from 'redux';
import {File} from 'akeneoassetmanager/domain/model/file';
import AttributeIdentifier from 'akeneoassetmanager/domain/model/attribute/identifier';
import TransformationCollection from 'akeneoassetmanager/domain/model/asset-family/transformation/transformation-collection';

export interface EditionFormState {
  state: FormState;
  data: AssetFamily;
  errors: ValidationError[];
}

const stateReducer = formState('assetFamily', 'ASSET_FAMILY_EDITION_UPDATED', 'ASSET_FAMILY_EDITION_RECEIVED');

const dataReducer = (
  state: AssetFamily = createEmptyAssetFamily(),
  {
    type,
    assetFamily,
    value,
    locale,
    image,
    attributeAsMainMedia,
    transformations,
  }: {
    type: string;
    assetFamily: AssetFamily;
    value: string;
    locale: string;
    image: File;
    attributeAsMainMedia: AttributeIdentifier;
    transformations: TransformationCollection;
  }
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
    case 'ASSET_FAMILY_EDITION_ATTRIBUTE_AS_MAIN_MEDIA_UPDATED':
      state = {...state, attributeAsMainMedia};
      break;
    case 'ASSET_FAMILY_EDITION_TRANSFORMATIONS_UPDATED':
      state = {...state, transformations};
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
