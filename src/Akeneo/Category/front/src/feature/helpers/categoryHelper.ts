import {clone, cloneDeep, identity, isEqual, sortBy} from 'lodash/fp';
import {LabelCollection, LocaleCode, ChannelCode} from '@akeneo-pim-community/shared';
import {
  Attribute,
  attributeDefaultValues,
  buildCompositeKey,
  CategoryAttributes,
  CategoryAttributeValueData,
  CategoryImageAttributeValueData,
  CategoryProperties,
  EnrichCategory,
  File,
  Template,
} from '../models';
import {CategoryPermission, CategoryPermissions} from '../models/CategoryPermission';

function labelsAreEqual(l1: LabelCollection, l2: LabelCollection): boolean {
  // maybe too strict of simplistic, to adjust
  return isEqual(l1, l2);
}

const sort = sortBy<number>(identity);

function isEqualUnordered(permissions1: CategoryPermission[], peremissions2: CategoryPermission[]): boolean {
  const permissions1Ids = permissions1?.map(permission => permission.id);
  const permissions2Ids = peremissions2?.map(permission => permission.id);
  return isEqual(sort(permissions1Ids), sort(permissions2Ids));
}

export function permissionsAreEqual(cp1: CategoryPermissions, cp2: CategoryPermissions): boolean {
  return (
    isEqualUnordered(cp1.view, cp2.view) && isEqualUnordered(cp1.edit, cp2.edit) && isEqualUnordered(cp1.own, cp2.own)
  );
}

function attributesAreEqual(a1: CategoryAttributes, a2: CategoryAttributes): boolean {
  return isEqual(a1, a2);
}

function propertiesAreEqual(p1: CategoryProperties, p2: CategoryProperties): boolean {
  return p1.code === p2.code && labelsAreEqual(p1.labels, p2.labels);
}

export function categoriesAreEqual(c1: EnrichCategory, c2: EnrichCategory): boolean {
  return (
    c1.id === c2.id &&
    propertiesAreEqual(c1.properties, c2.properties) &&
    permissionsAreEqual(c1.permissions, c2.permissions) &&
    attributesAreEqual(c1.attributes, c2.attributes)
  );
}

export function getAttributeValue(
  attributes: CategoryAttributes,
  attribute: Attribute,
  channelCode: ChannelCode,
  localeCode: LocaleCode
): CategoryAttributeValueData | undefined {
  const compositeKey = buildCompositeKey(attribute, channelCode, localeCode);
  const value = attributes[compositeKey];
  return value ? value.data : undefined;
}

/**
 * return a copy of the given category where all attributes defined in template are valued
 * @param category the category to populate
 * @param template the template describing the attribute of this category
 * @param channels the channels to consider when generating default values
 * @param locales the locales to consider when generating default values
 * @return the populated category
 */
function populateCategoryAttributes(
  category: EnrichCategory,
  template: Template | null | undefined,
  channels: ChannelCode[],
  locales: LocaleCode[]
): EnrichCategory {
  const fixedCategory = clone(category);

  if (fixedCategory.attributes === null) {
    fixedCategory.attributes = {};
  }

  template?.attributes?.forEach((attribute: Attribute) => {
    const {code} = attribute;
    if (fixedCategory.attributes.hasOwnProperty(code)) return;

    fixedCategory.attributes = {
      ...buildCategoryAttributeValues(attribute, channels, locales),
      // attributes values coming from the category in arguments must take precedence over generared ones
      ...fixedCategory.attributes,
    };
  });

  // [DEPRECATED] keep this filter to manage uncleaned data from previous code version
  // attribute_codes is a deprecated entry in attributes,
  // should not come to front via GET
  // but if it does we should ignore it (would break POSTing)
  delete fixedCategory.attributes['attribute_codes'];

  return fixedCategory;
}

/**
 * Generate a CategoryAttributes structure for a given attribute in the requested locales.
 * If the attribute in not localizable, then only one attribute value will be generated.
 * @param attribute the attribute for which we want to geenrate values
 * @param channels the channels to consider to build attribute values
 * @param locales the locales to consider to build attribute values
 * @returns the attributes values
 */
function buildCategoryAttributeValues(
  attribute: Attribute,
  channels: ChannelCode[],
  locales: LocaleCode[]
): CategoryAttributes {
  const attributesValues: {
    [key: string]: {
      data: CategoryAttributeValueData;
      channel: string | null;
      locale: string | null;
      attribute_code: string;
    };
  } = {};
  const applicableChannels = attribute.is_scopable ? channels : [null];
  const applicableLocales = attribute.is_localizable ? locales : [null];
  for (const channel of applicableChannels) {
    for (const locale of applicableLocales) {
      const key = buildCompositeKey(attribute, channel, locale);
      const keyNoLocale = locale === null ? key : buildCompositeKey(attribute);
      attributesValues[key] = {
        data: attributeDefaultValues[attribute.type],
        channel: channel,
        locale: locale,
        attribute_code: keyNoLocale,
      };
    }
  }
  return attributesValues;
}

/**
 * Ensures no category field is null and ensures attributes values are populated.
 */
export function populateCategory(
  category: EnrichCategory,
  template: Template | null | undefined,
  channels: ChannelCode[],
  locales: LocaleCode[]
): EnrichCategory {
  let populated = cloneDeep(category);
  if (category.permissions === null) {
    populated.permissions = {view: [], edit: [], own: []};
  }
  if (category.properties.labels === null) {
    populated.properties.labels = {};
  }

  populated = populateCategoryAttributes(populated, template, channels, locales);

  return populated;
}

export const convertCategoryImageAttributeValueDataToFileInfo = (
  valueData: CategoryImageAttributeValueData | null
): File => {
  if (valueData === null) {
    return null;
  }

  return {
    size: valueData.size,
    filePath: valueData.file_path,
    mimeType: valueData.mime_type,
    extension: valueData.extension,
    originalFilename: valueData.original_filename,
  };
};

export const convertFileInfoToCategoryImageAttributeValueData = (
  value: File
): CategoryImageAttributeValueData | null => {
  if (value === null) {
    return null;
  }

  return {
    size: value.size,
    file_path: value.filePath,
    mime_type: value.mimeType,
    extension: value.extension,
    original_filename: value.originalFilename,
  };
};
