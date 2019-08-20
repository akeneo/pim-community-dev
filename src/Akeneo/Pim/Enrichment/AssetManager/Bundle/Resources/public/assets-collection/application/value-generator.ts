import {
  ValueCollection,
  Value,
  Product,
  LegacyValueCollection,
  Meta,
  CategoryCode,
  LegacyValue,
} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/values';
import {Attribute, AttributeCode} from 'akeneopimenrichmentassetmanager/platform/model/structure/attribute';
import {
  fetchPermissions,
  AttributeGroupPersmissions,
  LocalePermissions,
  CategoryPermissions,
  isLocaleEditable,
  isAttributeGroupEditable,
  Permissions,
} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/permission';
import {fetchAssetAttributes} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/attribute';

const transformValues = (legacyValues: LegacyValueCollection, assetAttributes: Attribute[]): ValueCollection => {
  const attributeCodes = assetAttributes.map((attribute: Attribute) => attribute.code);
  const assetValueAttributeCodes = Object.keys(legacyValues).filter((attributeCode: AttributeCode) =>
    attributeCodes.includes(attributeCode)
  );

  return assetValueAttributeCodes.reduce((result: ValueCollection, attributeCode: AttributeCode) => {
    const attribute = assetAttributes.find(attribute => attribute.code === attributeCode);
    if (undefined === attribute) return result;

    const values = legacyValues[attributeCode].map(
      (legacyValue: LegacyValue): Value => ({
        attribute,
        locale: legacyValue.locale,
        channel: legacyValue.scope,
        data: legacyValue.data,
        editable: true,
      })
    );

    return result.concat(values);
  }, []);
};

const generate = async (product: Product): Promise<ValueCollection> => {
  const assetAttributes: Attribute[] = await fetchAssetAttributes();
  let valueCollection: ValueCollection = transformValues(product.values, assetAttributes);

  const permissions: Permissions = await fetchPermissions();
  valueCollection = filterAttributeGroups(valueCollection, permissions.attribute_groups);
  valueCollection = filterLocales(valueCollection, permissions.locales);
  valueCollection = filterReadOnlyAttribute(valueCollection);
  valueCollection = filterParentAttribute(valueCollection, product.meta);
  valueCollection = filterCategories(valueCollection, product.categories, permissions.categories);

  return valueCollection;
};

const filterAttributeGroups = (
  values: ValueCollection,
  attributeGroupPermissions: AttributeGroupPersmissions
): ValueCollection => {
  return values.map((value: Value) => {
    return {
      ...value,
      editable: value.editable && isAttributeGroupEditable(attributeGroupPermissions, value.attribute.group),
    };
  });
};

const filterLocales = (values: ValueCollection, localePermissions: LocalePermissions): ValueCollection => {
  return values.map((value: Value) => {
    return {
      ...value,
      editable: value.editable && isLocaleEditable(localePermissions, value.locale),
    };
  });
};

const filterReadOnlyAttribute = (values: ValueCollection): ValueCollection => {
  return values.map((value: Value) => ({
    ...value,
    editable: value.editable && !value.attribute.is_read_only,
  }));
};

const filterParentAttribute = (values: ValueCollection, meta: Meta): ValueCollection => {
  if (meta.level === null) return values;

  return values.map((value: Value) => ({
    ...value,
    editable: value.editable && meta.attributes_for_this_level.includes(value.attribute.code),
  }));
};

const filterCategories = (
  values: ValueCollection,
  categories: CategoryCode[],
  categoryPermissions: CategoryPermissions
): ValueCollection => {
  if (categories.length === 0) return values;

  const categoryRight = categories.some((categoryCode: string) =>
    categoryPermissions.EDIT_ITEMS.includes(categoryCode)
  );

  return values.map((value: Value) => ({
    ...value,
    editable: value.editable && categoryRight,
  }));
};

export default generate;
