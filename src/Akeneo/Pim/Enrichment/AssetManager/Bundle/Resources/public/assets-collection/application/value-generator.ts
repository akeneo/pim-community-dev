const fetcherRegistry = require('pim/fetcher-registry');
import promisify from 'akeneoassetmanager/tools/promisify';

type Value = {
  attribute: any,
  locale: string|null,
  channel: string|null,
  data: any,
  editable: boolean
};

type ValueCollection = Value[];

const transformValues = (legacyValues: any, assetAttributes: any[]): ValueCollection => {
  const attributeCodes = assetAttributes.map((attribute: any) => attribute.code);
  const assetValueAttributeCodes = Object.keys(legacyValues).filter(
    (attributeCode: string) => attributeCodes.includes(attributeCode)
  );

  return assetValueAttributeCodes.reduce((result: ValueCollection, key: string) => {
    const attribute = assetAttributes.find((attribute) => attribute.code === key);

    const values = legacyValues[key].map((legacyValue: any) => ({
      attribute: attribute,
      locale: legacyValue.locale,
      channel: legacyValue.scope,
      data: legacyValue.data,
      editable: true
    }));

    return result.concat(values);
  }, []);
};

const generate = async (product: any) => {
  const assetAttributes = await fetchAssetAttributes();
  let valueCollection = transformValues(product.values, assetAttributes);

  const permissions = await fetchPermissions();
  valueCollection = filterAttributeGroups(valueCollection, permissions.attribute_groups);
  valueCollection = filterLocales(valueCollection, permissions.locales);
  valueCollection = filterReadOnlyAttribute(valueCollection);
  valueCollection = filterParentAttribute(valueCollection, product.meta);
  valueCollection = filterCategories(valueCollection, product.categories, permissions.categories);

  return valueCollection;
};

const filterAttributeGroups = (values: ValueCollection, attributeGroupPermissions: any): ValueCollection => {
  return values.map((value: Value) => ({
    ...value,
      editable: value.editable && attributeGroupPermissions.find(
        (attributeGroupPermission: any) => attributeGroupPermission.code === value.attribute.group
      ).edit
  }));
};

const filterLocales = (values: ValueCollection, localePermissions: any): ValueCollection => {
  return values.map((value: Value) => ({
    ...value,
    editable: value.editable && (
      value.locale === null
      || localePermissions.find((localePermission: any) => localePermission.code === value.locale).edit
    )
  }));
};

const filterReadOnlyAttribute = (values: ValueCollection): ValueCollection => {
  return values.map((value: Value) => ({
    ...value,
    editable: value.editable && !value.attribute.is_read_only
  }));
};

const filterParentAttribute = (values: ValueCollection, meta: any): ValueCollection => {
  if (meta.level === null) return values;

  return values.map((value: Value) => ({
    ...value,
    editable: value.editable && meta.attributes_for_this_level.includes(value.attribute.code)
  }));
};

const filterCategories = (values: ValueCollection, categories: string[], categoryPermissions: any): ValueCollection => {
  if (categories.length === 0) return values;

  const categoryRight = categories.some((categoryCode: string) => categoryPermissions.EDIT_ITEMS.includes(categoryCode));

  return values.map((value: Value) => ({
    ...value,
    editable: value.editable && categoryRight
  }));
};

const fetchPermissions = async (): Promise<any> => {
  return promisify(fetcherRegistry.getFetcher('permission').fetchAll());
};

const fetchAssetAttributes = async (): Promise<any> => {
  return promisify(fetcherRegistry.getFetcher('attribute').fetchByTypes(['akeneo_asset_multiple_link']));
};

export default generate;
