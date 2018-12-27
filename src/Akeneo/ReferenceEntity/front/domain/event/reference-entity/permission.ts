import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import {PermissionConfiguration} from 'akeneoreferenceentity/tools/component/permission';

export const permissionEditionReceived = (permission: PermissionConfiguration) => {
  return {type: 'PERMISSION_EDITION_RECEIVED', permission};
};
export const permissionEditionUpdated = (permission: PermissionConfiguration) => {
  return {type: 'PERMISSION_EDITION_PERMISSION_UPDATED', permission};
};
export const permissionEditionSubmission = () => {
  return {type: 'PERMISSION_EDITION_SUBMISSION'};
};

export const permissionEditionSucceeded = () => {
  return {type: 'PERMISSION_EDITION_SUCCEEDED'};
};

export const permissionEditionErrorOccured = (errors: ValidationError[]) => {
  return {type: 'PERMISSION_EDITION_ERROR_OCCURED', errors};
};
