import {PermissionConfiguration} from 'akeneoreferenceentity/tools/component/permission';
import {FormState, createFormState} from 'akeneoreferenceentity/application/reducer/state';
import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';

export interface PermissionState {
  data: PermissionConfiguration;
  state: FormState;
  errors: ValidationError[];
}

const initState = () => ({
  data: {},
  state: createFormState(),
  errors: [],
});

const isDirty = (state: PermissionState, newData: PermissionConfiguration) => {
  return state.state.originalData !== JSON.stringify(newData);
};

const permission = (
  state: PermissionState = initState(),
  {type, permission, errors}: {type: string; permission: PermissionConfiguration; errors: ValidationError[]}
) => {
  switch (type) {
    case 'PERMISSION_EDITION_RECEIVED':
      state = {...state, data: permission, state: {isDirty: false, originalData: JSON.stringify(permission)}};
      break;
    case 'PERMISSION_EDITION_SUBMISSION':
      state = {...state, errors: []};
      break;
    case 'PERMISSION_EDITION_PERMISSION_UPDATED':
      state = {...state, data: permission, state: {...state.state, isDirty: isDirty(state, state.data)}};
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
