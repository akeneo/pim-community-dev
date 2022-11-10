import {useCallback, useContext, useEffect, useState} from 'react';
import {set, cloneDeep} from 'lodash/fp';
import {NotificationLevel, useNotify, useRouter, useTranslate} from '@akeneo-pim-community/shared';

import {saveEditCategoryForm} from '../infrastructure';
import {useCategory} from './useCategory';
import {EditCategoryContext} from '../components';
import {
  buildCompositeKey,
  Attribute,
  CategoryAttributeValueData,
  CategoryPermissions,
  EnrichCategory,
  Template,
} from '../models';
import {alterPermissionsConsistently, categoriesAreEqual, populateCategory} from '../helpers';
import {useTemplateByTemplateUuid} from "./useTemplateByTemplateUuid";

const useEditCategoryForm = (categoryId: number) => {
  const router = useRouter();
  const notify = useNotify();
  const translate = useTranslate();

  const {load: loadCategory, category: fetchedCategory, status: categoryStatus} = useCategory(categoryId);

  const {data: template, status: templateStatus} = useTemplateByTemplateUuid(fetchedCategory?.template_uuid ?? null)

  const [category, setCategory] = useState<EnrichCategory | null>(null);
  const [categoryEdited, setCategoryEdited] = useState<EnrichCategory | null>(null);

  const [applyPermissionsOnChildren, setApplyPermissionsOnChildren] = useState(true);

  const [historyVersion, setHistoryVersion] = useState<number>(0);
  const {setCanLeavePage, locales} = useContext(EditCategoryContext);

  const isModified =
    categoryStatus === 'fetched' &&
    !!category &&
    !!categoryEdited &&
    !categoriesAreEqual(category, categoryEdited);

  const initializeEditionState = useCallback(function (category: EnrichCategory, template: Template | null, locales) {
    const populated = populateCategory(category, template, Object.keys(locales));
    setCategory(populated);
    setCategoryEdited(cloneDeep(populated));
  }, []);

  // fetching the category
  useEffect(() => {
    loadCategory();
  }, [categoryId]);

  // initializing category edition state
  useEffect(() => {
    if (fetchedCategory === null) return;
    initializeEditionState(fetchedCategory, template ?? null, locales);
  }, [fetchedCategory, template, locales]);

  useEffect(() => {
    setCanLeavePage(!isModified);
  }, [setCanLeavePage, isModified]);

  // If there are unsaved changes then user will be asked confirmation to leave
  // If the edition form component is unmounted, it means that the user confirmed
  // CategorySettings Component boolean canLeavePage must be reset
  // so that we have no subsequent ghostly warnings about it
  useEffect(
    () => () => {
      setCanLeavePage(true);
    },
    [setCanLeavePage]
  );

  const saveCategory = useCallback(async () => {
    if (categoryEdited === null) {
      return;
    }

    const response = await saveEditCategoryForm(router, categoryEdited, {
      applyPermissionsOnChildren,
      populateResponseCategory: (category: EnrichCategory) =>
        populateCategory(category, template, Object.keys(locales)),
    });

    if (response.success) {
      initializeEditionState(response.category, template ?? null, locales);
      setHistoryVersion((prevVersion: number) => prevVersion + 1);
      notify(NotificationLevel.SUCCESS, translate('pim_enrich.entity.category.content.edit.success'));
    } else {
      notify(NotificationLevel.ERROR, translate('pim_enrich.entity.category.content.edit.fail'));
    }
  }, [
    router,
    categoryEdited,
    applyPermissionsOnChildren,
    initializeEditionState,
    translate,
    notify,
    locales,
    template,
  ]);

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

  const onChangeAttribute = useCallback(
    (attribute: Attribute, localeCode: string | null, attributeValue: CategoryAttributeValueData) => {
      if (categoryEdited === null) {
        return;
      }

      const compositeKey = buildCompositeKey(attribute, localeCode);
      const compositeKeyWithoutLocale = buildCompositeKey(attribute);

      const value = {
        data: attributeValue,
        locale: attribute.is_localizable ? localeCode : null,
        attribute_code: compositeKeyWithoutLocale,
      };

      const newCategoryEdited = set(['attributes', compositeKey], value, categoryEdited);
      if (categoriesAreEqual(categoryEdited, newCategoryEdited)) {
        return;
      }

      setCategoryEdited(newCategoryEdited);
    },
    [categoryEdited]
  );

  return {
    categoryFetchingStatus: categoryStatus,
    templateFetchingStatus: templateStatus,
    category: categoryEdited,
    template,
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
