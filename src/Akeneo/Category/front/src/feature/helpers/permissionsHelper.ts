import {EnrichCategory} from '../models';

const computeNewViewPermissions = (originalFormData: EnrichCategory, values: string[]) => {
  let newFormData = updateFormDataWithNewValues(originalFormData, values, 'view');
  const {removedPermission} = computeDifferencesBetweenPermissions(originalFormData, newFormData, 'view');
  if (removedPermission) {
    newFormData = removePermissionByType('edit', removedPermission, newFormData);
    newFormData = removePermissionByType('own', removedPermission, newFormData);
  }

  return newFormData;
};

const computeNewEditPermissions = (originalFormData: EnrichCategory, values: string[]) => {
  let newFormData = updateFormDataWithNewValues(originalFormData, values, 'edit');
  const {removedPermission, addedPermission} = computeDifferencesBetweenPermissions(
    originalFormData,
    newFormData,
    'edit'
  );
  if (removedPermission) {
    newFormData = removePermissionByType('own', removedPermission, newFormData);
  }
  if (addedPermission) {
    newFormData = addPermissionByType('view', addedPermission, newFormData);
  }

  return newFormData;
};

const computeNewOwnPermissions = (originalFormData: EnrichCategory, values: string[]) => {
  let newFormData = updateFormDataWithNewValues(originalFormData, values, 'own');
  const {addedPermission} = computeDifferencesBetweenPermissions(originalFormData, newFormData, 'own');
  if (addedPermission) {
    newFormData = addPermissionByType('view', addedPermission, newFormData);
    newFormData = addPermissionByType('edit', addedPermission, newFormData);
  }

  return newFormData;
};

const updateFormDataWithNewValues = (
  originalFormData: EnrichCategory,
  values: string[],
  type: 'view' | 'edit' | 'own'
) => {
  if (!originalFormData.permissions) {
    return originalFormData;
  }

  return {
    ...originalFormData,
    permissions: {
      ...originalFormData.permissions,
      [type]: values
    },
  };
};

const removePermissionByType = (type: 'view' | 'edit' | 'own', permissionId: number, newPermissions: any) => {
  const newValues = newPermissions.permissions[type].filter((permission: number) => permission !== permissionId);

  return {
    ...newPermissions,
    permissions: {
      ...newPermissions.permissions,
      [type]: newValues
    },
  };
};

const addPermissionByType = (type: 'view' | 'edit' | 'own', permissionId: number, newPermissions: any) => {
  let newValues = [...newPermissions.permissions[type]];
  newValues.push(permissionId);
  newValues = Array.from(new Set(newValues)); //to remove duplicated values

  return {
    ...newPermissions,
    permissions: {
      ...newPermissions.permissions,
      [type]: newValues
    },
  };
};

const computeDifferencesBetweenPermissions = (permissionsA: any, permissionsB: any, type: string) => {
  return {
    removedPermission: permissionsA.permissions[type].filter(
      (permissionId: number) => !permissionsB.permissions[type].includes(permissionId)
    )[0],
    addedPermission: permissionsB.permissions[type].filter(
      (permissionId: number) => !permissionsA.permissions[type].includes(permissionId)
    )[0],
  };
};

export {computeNewViewPermissions, computeNewEditPermissions, computeNewOwnPermissions};
