import {Category, EditableCategoryProperties} from '../../models';
import {useCallback, useContext, useEffect, useState} from "react";
import {useBooleanState} from "akeneo-design-system";
import {editCategory} from "../../infrastructure/savers";
import {LabelCollection, NotificationLevel, useNotify, useTranslate} from "@akeneo-pim-community/shared";
import {EditCategoryForm} from "./useCategory";
import {EditCategoryContext} from "../../components";

// @todo rename?
// @todo move to a more suitable place
export type ValidationErrors = {
   [field: string]: string | LabelCollection;
}

const useEditCategory = (category: Category | null, formData: EditCategoryForm | null) => {
  const notify = useNotify();
  const translate = useTranslate();
  const [originalFormData, setOriginalFormData] = useState<EditCategoryForm | null>(formData);
  const [editedFormData, setEditedFormData] = useState<EditCategoryForm | null>(null);
  const [thereAreUnsavedChanges, setThereAreUnsavedChanges] = useState<boolean>(false);
  const {setCanLeavePage} = useContext(EditCategoryContext);

  // @todo find better names
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

  const onChangeCategoryLabel = (locale: string, label: string) => {
    if (editedFormData === null || !editedFormData.label.hasOwnProperty(locale)) {
      return;
    }

    const editedLabel = {...editedFormData.label[locale], value: label};
    setEditedFormData({...editedFormData, label: {...editedFormData.label, [locale]: editedLabel}});
  };

  const saveCategory = useCallback(async () => {
    if (category === null || editedFormData === null) {
      return;
    }

    const response = await editCategory(category.id, editedFormData);

    if (response.success) {
      notify(NotificationLevel.SUCCESS, translate('pim_enrich.entity.category.content.edit.success'));
      setOriginalFormData(response.form);
    } else {
      notify(NotificationLevel.ERROR, translate('pim_enrich.entity.category.content.edit.fail'));
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
      setThereAreUnsavedChanges(haveLabelsBeenChanged());
    }
  }, [editedFormData]);

  useEffect(() => {
    setCanLeavePage(!thereAreUnsavedChanges);
  }, [thereAreUnsavedChanges]);

  return {
    editedFormData,
    onChangeCategoryLabel,
    thereAreUnsavedChanges,
    requestSave
  };
};

export {useEditCategory}
