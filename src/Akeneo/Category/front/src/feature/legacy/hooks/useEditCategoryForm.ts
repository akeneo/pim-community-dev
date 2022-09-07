import {useCallback, useContext, useEffect, useState} from 'react';
import {saveEditCategoryForm} from '../infrastructure';
import {NotificationLevel, useNotify, useRouter, useTranslate} from '@akeneo-pim-community/shared';
import {EditCategoryForm, useCategory} from './useCategory';
import {EditCategoryContext} from '../components';
import {computeNewEditPermissions, computeNewOwnPermissions, computeNewViewPermissions} from '../helpers';
import {Category} from '../models';

const useEditCategoryForm = (categoryId: number) => {
  const router = useRouter();
  const notify = useNotify();
  const translate = useTranslate();
  const [categoryData, loadCategory, categoryLoadingStatus] = useCategory(categoryId);
  const [category, setCategory] = useState<Category | null>(null);
  const [originalFormData, setOriginalFormData] = useState<EditCategoryForm | null>(null);
  const [editedFormData, setEditedFormData] = useState<EditCategoryForm | null>(null);
  const [thereAreUnsavedChanges, setThereAreUnsavedChanges] = useState<boolean>(false);
  const [historyVersion, setHistoryVersion] = useState<number>(0);
  const {setCanLeavePage} = useContext(EditCategoryContext);

  const haveLabelsBeenChanged = useCallback((): boolean => {
    if (originalFormData === null || editedFormData === null) {
      return false;
    }

    for (const [locale, changedLabel] of Object.entries(editedFormData.label)) {
      if (!originalFormData.label.hasOwnProperty(locale) && changedLabel.value === '') {
        return false;
      }
      if (
        !originalFormData.label.hasOwnProperty(locale) ||
        originalFormData.label[locale].value !== changedLabel.value
      ) {
        return true;
      }
    }

    return false;
  }, [originalFormData, editedFormData]);

  const havePermissionsBeenChanged = useCallback((): boolean => {
    if (
      originalFormData === null ||
      editedFormData === null ||
      !originalFormData.permissions ||
      !editedFormData.permissions
    ) {
      return false;
    }

    return (
      JSON.stringify(originalFormData.permissions.view.value) !==
        JSON.stringify(editedFormData.permissions.view.value) ||
      JSON.stringify(originalFormData.permissions.edit.value) !==
        JSON.stringify(editedFormData.permissions.edit.value) ||
      JSON.stringify(originalFormData.permissions.own.value) !== JSON.stringify(editedFormData.permissions.own.value)
    );
  }, [originalFormData, editedFormData]);

  const onChangeCategoryLabel = (locale: string, label: string) => {
    if (editedFormData === null || !editedFormData.label.hasOwnProperty(locale)) {
      return;
    }

    const editedLabel = {...editedFormData.label[locale], value: label};
    setEditedFormData({...editedFormData, label: {...editedFormData.label, [locale]: editedLabel}});
  };

  const onChangePermissions = (type: string, values: any) => {
    if (editedFormData === null || !editedFormData.permissions) {
      return;
    }

    switch (type) {
      case 'view':
        const newViewPermissions = computeNewViewPermissions(editedFormData, values);
        setEditedFormData(newViewPermissions);
        break;
      case 'edit':
        const newEditPermissions = computeNewEditPermissions(editedFormData, values);
        setEditedFormData(newEditPermissions);
        break;
      case 'own':
        const newOwnPermissions = computeNewOwnPermissions(editedFormData, values);
        setEditedFormData(newOwnPermissions);
        break;
    }
  };

  const onChangeApplyPermissionsOnChildren = (value: any) => {
    if (editedFormData === null || !editedFormData.permissions) {
      return;
    }

    const editedApplyOnChildren = {...editedFormData.permissions.apply_on_children, value: value === true ? '1' : '0'};
    setEditedFormData({
      ...editedFormData,
      permissions: {...editedFormData.permissions, apply_on_children: editedApplyOnChildren},
    });
  };

  const saveCategory = useCallback(async () => {
    if (categoryData === null || editedFormData === null) {
      return;
    }

    const response = await saveEditCategoryForm(router, categoryData.category.id, editedFormData);

    if (response.success) {
      setHistoryVersion((prevVersion: number) => prevVersion + 1);
      notify(NotificationLevel.SUCCESS, translate('pim_enrich.entity.category.content.edit.success'));
      setOriginalFormData(response.form);
      setCategory(response.category);
    } else {
      notify(NotificationLevel.ERROR, translate('pim_enrich.entity.category.content.edit.fail'));
      const refreshedToken = {...editedFormData._token, value: response.form._token.value};
      setEditedFormData({...editedFormData, _token: refreshedToken});
    }
  }, [categoryData, editedFormData]);

  useEffect(() => {
    loadCategory();
  }, [categoryId]);

  useEffect(() => {
    if (categoryData === null) {
      return;
    }

    setOriginalFormData(categoryData.form);
    setCategory(categoryData.category);
  }, [categoryData]);

  useEffect(() => {
    if (originalFormData === null) {
      return;
    }

    // Because the value of "apply_on_children" is always returned as "1" by the backend, it should only be defined at the first load
    if (originalFormData.permissions && editedFormData !== null && editedFormData.permissions) {
      setEditedFormData({
        ...originalFormData,
        permissions: {...originalFormData.permissions, apply_on_children: editedFormData.permissions.apply_on_children},
      });
    } else {
      setEditedFormData({...originalFormData});
    }
  }, [originalFormData]);

  useEffect(() => {
    if (editedFormData !== null) {
      setThereAreUnsavedChanges(haveLabelsBeenChanged() || havePermissionsBeenChanged());
    }
  }, [editedFormData]);

  useEffect(() => {
    setCanLeavePage(!thereAreUnsavedChanges);
  }, [thereAreUnsavedChanges]);

  return {
    categoryLoadingStatus,
    category,
    formData: editedFormData,
    onChangeCategoryLabel,
    onChangePermissions,
    onChangeApplyPermissionsOnChildren,
    thereAreUnsavedChanges,
    saveCategory,
    historyVersion,
  };
};

export {useEditCategoryForm};
