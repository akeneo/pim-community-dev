const fetcherRegistry = require('pim/fetcher-registry');
import promisify from 'akeneoassetmanager/tools/promisify';
import {AttributeGroupCode} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/structure';
import {LocaleCode} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/context';
import {CategoryCode, LocaleReference} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/values';

export type AttributeGroupPersmission = {
  code: AttributeGroupCode;
  edit: boolean;
  view: boolean;
};
export type AttributeGroupPersmissions = AttributeGroupPersmission[];
export const isAttributeGroupEditable = (
  attributeGroupPermissions: AttributeGroupPersmissions,
  attributeGroupCode: AttributeGroupCode
) => {
  const permission = attributeGroupPermissions.find(
    (attributeGroupPermission: AttributeGroupPersmission) => attributeGroupPermission.code === attributeGroupCode
  );

  return undefined === permission || permission.edit;
};

export type LocalePermission = {
  code: LocaleCode;
  edit: boolean;
  view: boolean;
};
export type LocalePermissions = LocalePermission[];
export const isLocaleEditable = (localePermissions: LocalePermissions, locale: LocaleReference): boolean => {
  if (null === locale) {
    return true;
  }

  const permission = localePermissions.find((localePermission: LocalePermission) => localePermission.code === locale);

  return undefined === permission || permission.edit;
};

export type CategoryPermissions = {
  EDIT_ITEMS: CategoryCode[];
};

export type Permissions = {
  attribute_groups: AttributeGroupPersmissions;
  locales: LocalePermissions;
  categories: CategoryPermissions;
};

export const fetchPermissions = async (): Promise<Permissions> => {
  return promisify(fetcherRegistry.getFetcher('permission').fetchAll());
};
