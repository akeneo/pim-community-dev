import {AttributeWithRecommendation, Product} from '../../../../../domain';
import React from 'react';
import AttributesList from './AttributesList';

const __ = require('oro/translator');

interface AttributesListProps {
  product: Product;
  criterionCode: string;
  attributes: AttributeWithRecommendation[];
  locale: string;
}

const VariantAttributesList = ({product, criterionCode, attributes, locale}: AttributesListProps) => {

  const computeRootProductModelAttributesList = (attributes: AttributeWithRecommendation[]) => {
    let variantAttributes: string[] = [];
    product.meta.family_variant.variant_attribute_sets.forEach((value: any) => {
      variantAttributes = [...variantAttributes, ...value.attributes];
    });
    return attributes.filter((attribute: AttributeWithRecommendation) => {
      return !variantAttributes.includes(attribute.code);
    });
  };

  const getLevelAttributes = (attributes: AttributeWithRecommendation[], level: number) => {
    if (level === 0) {
      return computeRootProductModelAttributesList(attributes);
    }

    return attributes.filter((attribute: AttributeWithRecommendation) => {
      return product.meta.family_variant.variant_attribute_sets[level -1 ].attributes.includes(attribute.code);
    });
  };

  return (
    <div className="CriterionVariantRecommendations">
      {product.meta.variant_navigation
        // @ts-ignore
        .filter((_: any, index) => index <= product.meta.level)
        .map((variant: any, index: number) => {
          return (
            <div key={`variant-attributes-list-${index}`} className="attributeList">
              <span>{index > 0 ? variant.axes[locale] : __('pim_enrich.entity.product.module.variant_navigation.common')}</span>:&thinsp;
              <AttributesList product={product} criterionCode={criterionCode} attributes={getLevelAttributes(attributes, index)}/>
            </div>
          )
      })}
    </div>
  );
};

export default VariantAttributesList;
