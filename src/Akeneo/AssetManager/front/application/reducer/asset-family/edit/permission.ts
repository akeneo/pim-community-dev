import {FormState, createFormState} from 'akeneoassetmanager/application/reducer/state';
import {ValidationError} from '@akeneo-pim-community/shared';
import {NormalizedPermissionCollection} from 'akeneoassetmanager/domain/model/asset-family/permission';

export interface PermissionState {
  data: NormalizedPermissionCollection;
  state: FormState;
  errors: ValidationError[];
}

const initState = () => ({
  data: [],
  state: createFormState(),
  errors: [],
});

const isDirty = (state: PermissionState, newData: NormalizedPermissionCollection) => {
  return state.state.originalData !== JSON.stringify(newData);
};

const permission = (
  state: PermissionState = initState(),
  {type, permissions, errors}: {type: string; permissions: NormalizedPermissionCollection; errors: ValidationError[]}
) => {
  switch (type) {
    case 'PERMISSION_EDITION_RECEIVED':
      state = {...state, data: permissions, state: {isDirty: false, originalData: JSON.stringify(permissions)}};
      break;
    case 'PERMISSION_EDITION_SUBMISSION':
      state = {...state, errors: []};
      break;
    case 'PERMISSION_EDITION_PERMISSION_UPDATED':
      state = {...state, data: permissions, state: {...state.state, isDirty: isDirty(state, permissions)}};
      break;
    case 'PERMISSION_EDITION_ERROR_OCCURED':
      state = {...state, errors};
      break;
    default:
      break;
  }

  return state;
};

export default permission;
