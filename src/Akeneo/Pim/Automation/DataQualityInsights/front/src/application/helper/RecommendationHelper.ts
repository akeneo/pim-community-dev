import {uniq as _uniq} from 'lodash';
import AttributeWithRecommendation from '../../domain/AttributeWithRecommendation.interface';
import {CriterionEvaluationResult, Family, Product} from '../../domain';

const getAttributeLabel = (attributeCode: string, productFamilyInformation: Family | null, locale: string): string => {
  if (!productFamilyInformation || !productFamilyInformation.attributes) {
    return attributeCode;
  }

  const attributeItem = productFamilyInformation.attributes.find((item: any) => {
    return item.code === attributeCode;
  });

  if (!attributeItem || !attributeItem.labels || !attributeItem.labels[locale]) {
    return attributeCode;
  }

  return attributeItem.labels[locale];
};

const getAttributesListWithLabels = (
  attributes: string[],
  family: Family | null,
  locale?: string
): AttributeWithRecommendation[] => {
  const attributesWithLabels: AttributeWithRecommendation[] = [];

  attributes.forEach((attributeCode: string) => {
    let label: string = attributeCode;

    if (locale && family) {
      label = getAttributeLabel(attributeCode, family, locale);
    }

    attributesWithLabels.push({
      code: attributeCode,
      label,
    });
  });

  return attributesWithLabels;
};

const sortAttributesList = (attributesLabels: AttributeWithRecommendation[]): AttributeWithRecommendation[] => {
  return Object.values(attributesLabels).sort(
    (attribute1: AttributeWithRecommendation, attribute2: AttributeWithRecommendation) => {
      return attribute1.label.localeCompare(attribute2.label, undefined, {sensitivity: 'base'});
    }
  );
};

const computeRootProductModelAttributesList = (
  attributes: AttributeWithRecommendation[],
  product: Product
): AttributeWithRecommendation[] => {
  let variantAttributes: string[] = [];
  product.meta.family_variant.variant_attribute_sets.forEach((value: any) => {
    variantAttributes = [...variantAttributes, ...value.attributes];
  });

  return attributes.filter((attribute: AttributeWithRecommendation) => {
    return !variantAttributes.includes(attribute.code);
  });
};

const getLevelAttributes = (attributes: AttributeWithRecommendation[], level: number, product: Product) => {
  if (level === 0) {
    return computeRootProductModelAttributesList(attributes, product);
  }

  return attributes.filter((attribute: AttributeWithRecommendation) => {
    return product.meta.family_variant.variant_attribute_sets[level - 1].attributes.includes(attribute.code);
  });
};

const getAxisAttributesWithRecommendations = (criteria: CriterionEvaluationResult[]): string[] => {
  let attributes: string[] = [];

  criteria.map(criterion => {
    attributes = [...criterion.improvable_attributes, ...attributes];
  });

  return _uniq(attributes);
};

export {
  getAttributeLabel,
  getAttributesListWithLabels,
  sortAttributesList,
  getLevelAttributes,
  getAxisAttributesWithRecommendations,
};
