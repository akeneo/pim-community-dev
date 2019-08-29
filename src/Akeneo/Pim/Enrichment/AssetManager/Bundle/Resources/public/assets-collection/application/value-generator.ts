import {
  ValueCollection,
  Value,
  Product,
  LegacyValueCollection,
  Meta,
  CategoryCode,
  LegacyValue,
} from 'akeneopimenrichmentassetmanager/enrich/domain/model/product';
import {Attribute, AttributeCode} from 'akeneopimenrichmentassetmanager/platform/model/structure/attribute';
import {
  fetchPermissions,
  AttributeGroupPermission,
  LocalePermission,
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

/**
* We are generating a value collection from the product values to be able to use them in the asset collection.
* We are also applying filters on them to know if the value is editable or not regarding different criteria :
*    - if the attribute group has the edit permissions
*    - if the locale has the edit permission
*    - if it's a value of a read only attribute
*    - if it's a value of a parent attribute
*    - if the product category has the edit permission
*/
const generate = async (product: Product): Promise<ValueCollection> => {
  const assetAttributes: Attribute[] = await fetchAssetAttributes();
  let valueCollection: ValueCollection = transformValues(product.values, assetAttributes);

  const permissions: Permissions = await fetchPermissions();
  valueCollection = filterAttributeGroups(valueCollection, permissions.attributeGroups);
  valueCollection = filterLocales(valueCollection, permissions.locales);
  valueCollection = filterReadOnlyAttribute(valueCollection);
  valueCollection = filterParentAttribute(valueCollection, product.meta);
  valueCollection = filterCategories(valueCollection, product.categories, permissions.categories);

  return valueCollection;
};

const filterAttributeGroups = (
  values: ValueCollection,
  attributeGroupPermissions: AttributeGroupPermission[]
): ValueCollection => {
  return values.map((value: Value) => {
    return {
      ...value,
      editable: value.editable && isAttributeGroupEditable(attributeGroupPermissions, value.attribute.group),
    };
  });
};

const filterLocales = (values: ValueCollection, localePermissions: LocalePermission[]): ValueCollection => {
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
    editable: value.editable && !value.attribute.isReadOnly,
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
