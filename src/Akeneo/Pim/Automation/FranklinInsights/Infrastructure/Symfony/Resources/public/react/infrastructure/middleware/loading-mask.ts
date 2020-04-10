import {Store} from 'redux';
import {
  SAVED_FAMILY_MAPPING_FAIL,
  SAVED_FAMILY_MAPPING_SUCCESS
} from '../../application/action/family-mapping/save-family-mapping';

export const hideLoadingMaskMiddleware = (callback: () => void) => (_: Store) => (next: any) => (action: any) => {
  const result = next(action);

  if (action.type === SAVED_FAMILY_MAPPING_SUCCESS || action.type === SAVED_FAMILY_MAPPING_FAIL) {
    callback();
  }

  return result;
};
