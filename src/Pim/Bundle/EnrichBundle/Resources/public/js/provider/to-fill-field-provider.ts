const _ = require('underscore');

export const getMissingRequiredFields = (product: any, scope: string, locale: string): string[] => {
  const scopeMissingAttributes = _.findWhere(product.meta.required_missing_attributes, {channel: scope});
  if (undefined === scopeMissingAttributes) {
    return [];
  }

  const localeMissingAttributes = scopeMissingAttributes.locales[locale];
  if (undefined === localeMissingAttributes) {
    return [];
  }

  const missingAttributeCodes = localeMissingAttributes.missing.map((missing: any) => missing.code);
  const levelAttributeCodes = Object.keys(product.values);

  return missingAttributeCodes.filter((missingAttribute: string) => levelAttributeCodes.includes(missingAttribute));
};
