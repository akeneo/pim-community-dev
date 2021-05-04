import {ValidationError} from '@akeneo-pim-community/shared';
import {PermissionCollection} from 'akeneoassetmanager/domain/model/asset-family/permission';

export const permissionEditionReceived = (permissions: PermissionCollection) => {
  return {type: 'PERMISSION_EDITION_RECEIVED', permissions: permissions.normalize()};
};
export const permissionEditionUpdated = (permissions: PermissionCollection) => {
  return {type: 'PERMISSION_EDITION_PERMISSION_UPDATED', permissions: permissions.normalize()};
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
