import {useCallback, useContext, useEffect, useState} from 'react';
import {saveEditCategoryForm} from '../infrastructure';
import {NotificationLevel, useNotify, useRouter, useTranslate} from '@akeneo-pim-community/shared';
import {useCategory} from './useCategory';
import {EditCategoryContext} from '../components';
import {computeNewEditPermissions, computeNewOwnPermissions, computeNewViewPermissions} from '../helpers';
import {EnrichCategory} from '../models';

// @todo Add unit tests
const useEditCategoryForm = (categoryId: number) => {
  const router = useRouter();
  const notify = useNotify();
  const translate = useTranslate();
  const [categoryData, loadCategory, categoryLoadingStatus] = useCategory(categoryId);
  const [category, setCategory] = useState<EnrichCategory | null>(null);
  const [originalFormData, setOriginalFormData] = useState<EnrichCategory | null>(null);
  const [editedFormData, setEditedFormData] = useState<EnrichCategory | null>(null);
  const [thereAreUnsavedChanges, setThereAreUnsavedChanges] = useState<boolean>(false);
  const [historyVersion, setHistoryVersion] = useState<number>(0);
  const {setCanLeavePage} = useContext(EditCategoryContext);

  const haveLabelsBeenChanged = useCallback((): boolean => {
    if (originalFormData === null || editedFormData === null) {
      return false;
    }

    for (const [locale, changedLabel] of Object.entries(editedFormData.labels)) {
      if (!originalFormData.labels.hasOwnProperty(locale) && changedLabel === '') {
        return false;
      }
      if (
        !originalFormData.labels.hasOwnProperty(locale) ||
        originalFormData.labels[locale] !== changedLabel
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
      JSON.stringify(originalFormData.permissions.view) !==
        JSON.stringify(editedFormData.permissions.view) ||
      JSON.stringify(originalFormData.permissions.edit) !==
        JSON.stringify(editedFormData.permissions.edit) ||
      JSON.stringify(originalFormData.permissions.own) !== JSON.stringify(editedFormData.permissions.own)
    );
  }, [originalFormData, editedFormData]);

  const onChangeCategoryLabel = (locale: string, label: string) => {
    if (editedFormData === null || !editedFormData.labels.hasOwnProperty(locale)) {
      return;
    }

    setEditedFormData({
      ...editedFormData,
      labels: {...editedFormData.labels, [locale]: label}
    });
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

    setEditedFormData({
      ...editedFormData,
      permissions: {
        ...editedFormData.permissions,
        apply_on_children: value === true ? '1' : '0'
      },
    });
  };

  const saveCategory = useCallback(async () => {
    if (categoryData === null || editedFormData === null) {
      return;
    }

    const response = await saveEditCategoryForm(router, categoryData);

    if (response.success) {
      setHistoryVersion((prevVersion: number) => prevVersion + 1)
      notify(NotificationLevel.SUCCESS, translate('pim_enrich.entity.category.content.edit.success'));
      setOriginalFormData(response.category);
      setCategory(response.category);
    } else {
      notify(NotificationLevel.ERROR, translate('pim_enrich.entity.category.content.edit.fail'));
      // const refreshedToken = {...editedFormData._token, value: response.form._token.value};
      // setEditedFormData({...editedFormData, _token: refreshedToken});
    }
  }, [categoryData, editedFormData]);

  useEffect(() => {
    loadCategory();
  }, [categoryId]);

  useEffect(() => {
    if (categoryData === null) {
      return;
    }

    setOriginalFormData(categoryData);
    setCategory(categoryData);
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
    historyVersion
  };
};

export {useEditCategoryForm};
