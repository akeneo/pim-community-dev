import {useContext, useEffect, useState} from 'react';
import {cloneDeep, set} from 'lodash/fp';
import {Channel, Locale, NotificationLevel, useNotify, useRouter, useTranslate} from '@akeneo-pim-community/shared';
import {saveEditCategoryForm} from '../infrastructure';
import {useCategory} from './useCategory';
import {CanLeavePageContext, EditCategoryContext} from '../components';
import {Attribute, buildCompositeKey, CategoryAttributeValueData, EnrichCategory, Template} from '../models';
import {alterPermissionsConsistently, categoriesAreEqual, populateCategory} from '../helpers';
import {useTemplateByTemplateUuid} from './useTemplateByTemplateUuid';
import {CategoryPermissions} from '../models/CategoryPermission';
import {UserGroup} from './useFetchUserGroups';
import {DEACTIVATED_TEMPLATE} from '../models/ResponseStatus';
import {useHistory} from 'react-router';
import {useQueryClient} from 'react-query';

const useEditCategoryForm = (categoryId: number) => {
  const router = useRouter();
  const notify = useNotify();
  const translate = useTranslate();
  const history = useHistory();
  const queryClient = useQueryClient();

  const {load: loadCategory, category: fetchedCategory, status: categoryStatus} = useCategory(categoryId);

  const {data: template} = useTemplateByTemplateUuid(fetchedCategory?.template_uuid ?? null);

  const [category, setCategory] = useState<EnrichCategory | null>(null);
  const [categoryEdited, setCategoryEdited] = useState<EnrichCategory | null>(null);

  const [applyPermissionsOnChildren, setApplyPermissionsOnChildren] = useState(true);

  const [historyVersion, setHistoryVersion] = useState<number>(0);
  const {channels, locales} = useContext(EditCategoryContext);
  const {setCanLeavePage} = useContext(CanLeavePageContext);

  const isModified =
    categoryStatus === 'fetched' && !!category && !!categoryEdited && !categoriesAreEqual(category, categoryEdited);

  const initializeEditionState = (
    category: EnrichCategory,
    template: Template | null,
    channels: {[code: string]: Channel},
    locales: {[code: string]: Locale}
  ) => {
    const populated = populateCategory(category, template, Object.keys(channels), Object.keys(locales));
    setCategory(populated);
    setCategoryEdited(cloneDeep(populated));
  };

  // fetching the category
  useEffect(() => {
    loadCategory();
  }, [categoryId]);

  // initializing category edition state
  useEffect(() => {
    if (fetchedCategory === null) return;
    initializeEditionState(fetchedCategory, template ?? null, channels, locales);
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

  const saveCategory = async () => {
    if (categoryEdited === null) {
      return;
    }

    const response = await saveEditCategoryForm(router, categoryEdited, {
      applyPermissionsOnChildren,
      populateResponseCategory: (category: EnrichCategory) =>
        populateCategory(category, template, Object.keys(channels), Object.keys(locales)),
    });

    if (response.success) {
      initializeEditionState(response.category, template ?? null, channels, locales);
      setHistoryVersion((prevVersion: number) => prevVersion + 1);
      notify(NotificationLevel.SUCCESS, translate('pim_enrich.entity.category.content.edit.success'));

      // force to fetch attribute list from template to hide potentially deleted attributes
      await queryClient.invalidateQueries(['template', fetchedCategory?.template_uuid]);
    } else {
      notify(NotificationLevel.ERROR, response.error.message);
      if (response.error.code && response.error.code === DEACTIVATED_TEMPLATE) {
        history.push('/');
      }
    }
  };

  const onChangeCategoryLabel = (localeCode: string, label: string) => {
    if (categoryEdited === null) {
      return;
    }
    setCategoryEdited(set(['properties', 'labels', localeCode], label === '' ? null : label, categoryEdited));
  };

  const onChangePermissions = (userGroups: UserGroup[], type: keyof CategoryPermissions, values: number[]) => {
    if (categoryEdited === null) {
      return;
    }

    const consistentPermissions = alterPermissionsConsistently(userGroups, categoryEdited.permissions, {type, values});

    setCategoryEdited(set('permissions', consistentPermissions, categoryEdited));
  };

  const onChangeAttribute = (
    attribute: Attribute,
    channelCode: string | null,
    localeCode: string | null,
    attributeValue: CategoryAttributeValueData
  ) => {
    if (categoryEdited === null) {
      return;
    }

    const compositeKey = buildCompositeKey(attribute, channelCode, localeCode);
    const compositeKeyWithoutChannelAndLocale = buildCompositeKey(attribute);

    const value = {
      data: attributeValue,
      channel: attribute.is_scopable ? channelCode : null,
      locale: attribute.is_localizable ? localeCode : null,
      attribute_code: compositeKeyWithoutChannelAndLocale,
    };

    const newCategoryEdited = set(['attributes', compositeKey], value, categoryEdited);
    if (categoriesAreEqual(categoryEdited, newCategoryEdited)) {
      return;
    }

    setCategoryEdited(newCategoryEdited);
  };

  return {
    categoryFetchingStatus: categoryStatus,
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
