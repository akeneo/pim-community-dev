import {useCallback, useContext, useEffect, useState} from 'react';
import {set, cloneDeep} from 'lodash/fp';
import {NotificationLevel, useNotify, useRouter, useTranslate} from '@akeneo-pim-community/shared';

import {saveEditCategoryForm} from '../infrastructure';
import {useCategory} from './useCategory';
import {EditCategoryContext} from '../components';
import {
  buildCompositionKey,
  categoriesAreEqual,
  CategoryAttribute,
  CategoryAttributeValueData,
  CategoryPermissions,
  EnrichCategory,
} from '../models';
import {alterPermissionsConsistently} from '../helpers';

const useEditCategoryForm = (categoryId: number) => {
  const router = useRouter();
  const notify = useNotify();
  const translate = useTranslate();

  const useCategoryResult = useCategory(categoryId);
  const {status: categoryFetchingStatus, load: loadCategory} = useCategoryResult;

  let fetchedCategory: EnrichCategory | null = null;
  if (useCategoryResult.status === 'fetched') {
    fetchedCategory = useCategoryResult.category;
  }

  const [category, setCategory] = useState<EnrichCategory | null>(null);
  const [categoryEdited, setCategoryEdited] = useState<EnrichCategory | null>(null);

  const [applyPermissionsOnChildren, setApplyPermissionsOnChildren] = useState(true);

  const [historyVersion, setHistoryVersion] = useState<number>(0);
  const {setCanLeavePage} = useContext(EditCategoryContext);

  const isModified =
    useCategoryResult.status === 'fetched' &&
    !!category &&
    !!categoryEdited &&
    !categoriesAreEqual(category, categoryEdited);

  const initializeEditionState = useCallback(function (c: EnrichCategory) {
    setCategory(c);
    setCategoryEdited(cloneDeep(c));
  }, []);

  // fetching the category
  useEffect(() => {
    loadCategory();
  }, [categoryId, loadCategory]);

  // initializing category edition state
  useEffect(() => {
    fetchedCategory && initializeEditionState(fetchedCategory);
  }, [fetchedCategory, initializeEditionState]);

  useEffect(() => {
    setCanLeavePage(!isModified);
  }, [setCanLeavePage, isModified]);

  const saveCategory = useCallback(async () => {
    if (categoryEdited === null) {
      return;
    }

    const response = await saveEditCategoryForm(router, categoryEdited, {applyPermissionsOnChildren});

    if (response.success) {
      initializeEditionState(response.category);
      setHistoryVersion((prevVersion: number) => prevVersion + 1);
      notify(NotificationLevel.SUCCESS, translate('pim_enrich.entity.category.content.edit.success'));
    } else {
      notify(NotificationLevel.ERROR, translate('pim_enrich.entity.category.content.edit.fail'));
      // const refreshedToken = {...editedFormData._token, value: response.form._token.value};
      // setEditedFormData({...editedFormData, _token: refreshedToken});
    }
  }, [router, categoryEdited, applyPermissionsOnChildren, initializeEditionState, translate, notify]);

  const onChangeCategoryLabel = useCallback(
    (localeCode: string, label: string) => {
      if (categoryEdited === null) {
        return;
      }

      setCategoryEdited(set(['properties', 'labels', localeCode], label, categoryEdited));
    },
    [categoryEdited]
  );

  const onChangePermissions = (type: keyof CategoryPermissions, values: number[]) => {
    if (categoryEdited === null) {
      return;
    }

    const consistentPermissions = alterPermissionsConsistently(categoryEdited.permissions, {type, values});

    setCategoryEdited(set(['permissions'], consistentPermissions, categoryEdited));
  };

  const onChangeAttribute = (
    attribute: CategoryAttribute,
    localeCode: string | null,
    attributeValue: CategoryAttributeValueData
  ) => {
    if (categoryEdited === null) {
      return;
    }

    const compositeKey = buildCompositionKey(attribute, localeCode);
    const compositeKeyWithoutLocale = buildCompositionKey(attribute);

    const value = {
      data: attributeValue,
      locale: localeCode,
      attribute_code: compositeKeyWithoutLocale,
    };

    setCategoryEdited(set(['attributes', compositeKey], value, categoryEdited));
  };

  return {
    categoryFetchingStatus,
    category: categoryEdited,
    applyPermissionsOnChildren,
    onChangeCategoryLabel,
    onChangePermissions,
    onChangeAttribute,
    onChangeApplyPermissionsOnChildren: setApplyPermissionsOnChildren,
    isModified,
    saveCategory,
    historyVersion,
  };
};

export {useEditCategoryForm};
