import {EditCategoryForm} from '../models';

const computeNewViewPermissions = (originalFormData: EditCategoryForm, values: string[]) => {
  let newFormData = updateFormDataWithNewValues(originalFormData, values, 'view');
  const {removedPermission} = computeDifferencesBetweenPermissions(originalFormData, newFormData, 'view');
  if (removedPermission) {
    newFormData = removePermissionByType('edit', removedPermission, newFormData);
    newFormData = removePermissionByType('own', removedPermission, newFormData);
  }

  return newFormData;
};

const computeNewEditPermissions = (originalFormData: EditCategoryForm, values: string[]) => {
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

const computeNewOwnPermissions = (originalFormData: EditCategoryForm, values: string[]) => {
  let newFormData = updateFormDataWithNewValues(originalFormData, values, 'own');
  const {addedPermission} = computeDifferencesBetweenPermissions(originalFormData, newFormData, 'own');
  if (addedPermission) {
    newFormData = addPermissionByType('view', addedPermission, newFormData);
    newFormData = addPermissionByType('edit', addedPermission, newFormData);
  }

  return newFormData;
};

const updateFormDataWithNewValues = (
  originalFormData: EditCategoryForm,
  values: string[],
  type: 'view' | 'edit' | 'own'
) => {
  if (!originalFormData.permissions) {
    return originalFormData;
  }

  return {
    ...originalFormData,
    permissions: {...originalFormData.permissions, [type]: {...originalFormData.permissions[type], value: values}},
  };
};

const removePermissionByType = (type: 'view' | 'edit' | 'own', permissionId: number, newPermissions: any) => {
  const newValues = newPermissions.permissions[type].value.filter((permission: number) => permission !== permissionId);

  return {
    ...newPermissions,
    permissions: {...newPermissions.permissions, [type]: {...newPermissions.permissions[type], value: newValues}},
  };
};

const addPermissionByType = (type: 'view' | 'edit' | 'own', permissionId: number, newPermissions: any) => {
  let newValues = [...newPermissions.permissions[type].value];
  newValues.push(permissionId);
  newValues = Array.from(new Set(newValues)); //to remove duplicated values

  return {
    ...newPermissions,
    permissions: {...newPermissions.permissions, [type]: {...newPermissions.permissions[type], value: newValues}},
  };
};

const computeDifferencesBetweenPermissions = (permissionsA: any, permissionsB: any, type: string) => {
  return {
    removedPermission: permissionsA.permissions[type].value.filter(
      (permissionId: number) => !permissionsB.permissions[type].value.includes(permissionId)
    )[0],
    addedPermission: permissionsB.permissions[type].value.filter(
      (permissionId: number) => !permissionsA.permissions[type].value.includes(permissionId)
    )[0],
  };
};

export {computeNewViewPermissions, computeNewEditPermissions, computeNewOwnPermissions};
