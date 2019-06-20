import ValidationError from 'akeneoassetmanager/domain/model/validation-error';
import {
  NormalizedAssetFamilyCreation,
  createEmptyAssetFamilyCreation,
} from 'akeneoassetmanager/domain/model/asset-family/creation';
import sanitize from 'akeneoassetmanager/tools/sanitize';

export interface CreateState {
  active: boolean;
  data: NormalizedAssetFamilyCreation;
  errors: ValidationError[];
}

const initCreationState = (): CreateState => ({
  active: false,
  data: createEmptyAssetFamilyCreation().normalize(),
  errors: [],
});

export default (
  state: CreateState = initCreationState(),
  action: {type: string; locale: string; value: string; errors: ValidationError[]}
) => {
  switch (action.type) {
    case 'ASSET_FAMILY_CREATION_START':
      state = {...initCreationState(), active: true};
      break;

    case 'ASSET_FAMILY_CREATION_CODE_UPDATED':
      state = {
        ...state,
        data: {...state.data, code: action.value},
      };

      break;

    case 'ASSET_FAMILY_CREATION_LABEL_UPDATED':
      const previousLabel = state.data.labels[action.locale];
      const expectedSanitizedCode = sanitize(undefined === previousLabel ? '' : previousLabel);
      const code = expectedSanitizedCode === state.data.code ? sanitize(action.value) : state.data.code;

      state = {
        ...state,
        data: {...state.data, labels: {...state.data.labels, [action.locale]: action.value}, code},
      };

      break;

    case 'ASSET_FAMILY_CREATION_CANCEL':
    case 'DISMISS':
      state = {
        ...state,
        active: false,
      };
      break;

    case 'ASSET_FAMILY_CREATION_SUBMISSION':
      state = {
        ...state,
        errors: [],
      };
      break;

    case 'ASSET_FAMILY_CREATION_SUCCEEDED':
      state = {
        ...state,
        active: false,
      };
      break;

    case 'ASSET_FAMILY_CREATION_ERROR_OCCURED':
      state = {
        ...state,
        errors: action.errors,
      };
      break;
    default:
  }

  return state;
};
