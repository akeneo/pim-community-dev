import {
  SAVED_FAMILY_MAPPING_SUCCESS,
  SavedFamilyMappingSuccessAction,
  SAVED_FAMILY_MAPPING_FAIL,
  SavedFamilyMappingFailAction
} from '../../action/family-mapping/save-family-mapping';

export function savedFamilyMappingSuccess(): SavedFamilyMappingSuccessAction {
  return {
    type: SAVED_FAMILY_MAPPING_SUCCESS
  };
}

export function savedFamilyMappingFail(): SavedFamilyMappingFailAction {
  return {
    type: SAVED_FAMILY_MAPPING_FAIL
  };
}
