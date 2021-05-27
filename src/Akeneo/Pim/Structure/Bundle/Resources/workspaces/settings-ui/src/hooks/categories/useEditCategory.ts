import {Category, EditableCategoryProperties} from '../../models';
import {useCallback, useContext, useEffect, useState} from "react";
import {useBooleanState} from "akeneo-design-system";
import {saveEditCategoryForm} from "../../infrastructure/savers";
import {LabelCollection, NotificationLevel, useNotify, useTranslate} from "@akeneo-pim-community/shared";
import {EditCategoryForm} from "./useCategory";
import {EditCategoryContext} from "../../components";

// @todo Add unit tests
// @todo rename to useEditCategoryForm
const useEditCategory = (category: Category | null, formData: EditCategoryForm | null) => {
  const notify = useNotify();
  const translate = useTranslate();
  const [originalFormData, setOriginalFormData] = useState<EditCategoryForm | null>(formData);
  const [editedFormData, setEditedFormData] = useState<EditCategoryForm | null>(null);
  const [thereAreUnsavedChanges, setThereAreUnsavedChanges] = useState<boolean>(false);
  const {setCanLeavePage} = useContext(EditCategoryContext);

  // @todo remove and return saveCategory instead
  const [saveRequested, requestSave, resetRequestSave] = useBooleanState(false);

  const haveLabelsBeenChanged = useCallback((): boolean => {
    if (originalFormData === null || editedFormData === null) {
      return false;
    }

    for (const [locale, changedLabel] of Object.entries(editedFormData.label)) {
      if (!originalFormData.label.hasOwnProperty(locale) && changedLabel.value === '') {
        return false;
      }
      if (!originalFormData.label.hasOwnProperty(locale) || originalFormData.label[locale].value !== changedLabel.value) {
        return true;
      }
    }

    return false;
  }, [originalFormData, editedFormData]);

  const havePermissionsBeenChanged = useCallback((): boolean => {
    if (originalFormData === null || editedFormData === null || !originalFormData.permissions || !editedFormData.permissions) {
      return false;
    }

    return JSON.stringify(originalFormData.permissions.view.value) != JSON.stringify(editedFormData.permissions.view.value)
      || JSON.stringify(originalFormData.permissions.edit.value) != JSON.stringify(editedFormData.permissions.edit.value)
      || JSON.stringify(originalFormData.permissions.own.value) != JSON.stringify(editedFormData.permissions.own.value);

  }, [originalFormData, editedFormData]);

  const onChangeCategoryLabel = (locale: string, label: string) => {
    if (editedFormData === null || !editedFormData.label.hasOwnProperty(locale)) {
      return;
    }

    const editedLabel = {...editedFormData.label[locale], value: label};
    setEditedFormData({...editedFormData, label: {...editedFormData.label, [locale]: editedLabel}});
  };

  const onChangePermissions = (type: string, values: any) => {
    if (editedFormData === null || !editedFormData.permissions || !editedFormData.permissions.hasOwnProperty(type)) {
      return;
    }

    const editedPermission = {...editedFormData.permissions[type], value: values};
    setEditedFormData({...editedFormData, permissions: {...editedFormData.permissions, [type]: editedPermission}});
  };

  const onChangeApplyPermissionsOnChilren = (value: any) => {
    if (editedFormData === null || !editedFormData.permissions) {
      return;
    }

    const editedApplyOnChildren = {...editedFormData.permissions.apply_on_children, value: value === true ? '1' : '0'};
    setEditedFormData({
      ...editedFormData,
      permissions: {...editedFormData.permissions, apply_on_children: editedApplyOnChildren}
    });
  }

  const saveCategory = useCallback(async () => {
    if (category === null || editedFormData === null) {
      return;
    }

    const response = await saveEditCategoryForm(category.id, editedFormData);

    if (response.success) {
      notify(NotificationLevel.SUCCESS, translate('pim_enrich.entity.category.content.edit.success'));
      // @todo @fixme the form data returned are not always up-to-date with the backend for the permissions
      // ex:  Add a user-group in "Edit" and save. The user-group will be automatically also added in "View" backend side
      //      But the returned form data still not contain this user-group in the "View" permissions values
      //      Refreshing the page will display the added user-group in both "View" and "Edit"
      setOriginalFormData(response.form);
    } else {
      notify(NotificationLevel.ERROR, translate('pim_enrich.entity.category.content.edit.fail'));
      const refreshedToken = {...editedFormData._token, value: response.form._token.value};
      setEditedFormData({...editedFormData, _token: refreshedToken});
    }
  }, [category, editedFormData]);

  useEffect(() => {
    if (formData === null) {
      return;
    }

    setOriginalFormData(formData);

  }, [formData]);

  useEffect(() => {
    if (originalFormData === null) {
      return;
    }

    setEditedFormData({...originalFormData});

  }, [originalFormData]);

  useEffect(() => {
    if (!saveRequested) {
      return;
    }
    saveCategory();
    resetRequestSave();
  }, [saveRequested]);

  useEffect(() => {
    if (editedFormData !== null) {
      setThereAreUnsavedChanges(haveLabelsBeenChanged() || havePermissionsBeenChanged());
    }
  }, [editedFormData]);

  useEffect(() => {
    setCanLeavePage(!thereAreUnsavedChanges);
  }, [thereAreUnsavedChanges]);

  return {
    editedFormData,
    onChangeCategoryLabel,
    onChangePermissions,
    onChangeApplyPermissionsOnChilren,
    thereAreUnsavedChanges,
    requestSave
  };
};

export {useEditCategory}
