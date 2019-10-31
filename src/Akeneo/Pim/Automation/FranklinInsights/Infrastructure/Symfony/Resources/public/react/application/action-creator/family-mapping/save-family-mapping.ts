import {
  SAVED_FAMILY_MAPPING_SUCCESS,
  SavedFamilyMappingSuccessAction
} from '../../action/family-mapping/save-family-mapping';

export function savedFamilyMappingSuccess(): SavedFamilyMappingSuccessAction {
  return {
    type: SAVED_FAMILY_MAPPING_SUCCESS
  };
}
