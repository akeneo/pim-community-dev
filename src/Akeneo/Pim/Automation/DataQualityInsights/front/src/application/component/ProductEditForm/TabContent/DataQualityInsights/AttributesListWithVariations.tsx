import {AttributeWithRecommendation, Evaluation, Product} from '../../../../../domain';
import React from 'react';
import AttributesList from './AttributesList';
import {isRootProductModel} from "../../../../helper/ProductEditForm/Product";
import {ROOT_PRODUCT_MODEL_LEVEL} from "../../../../constant";

const __ = require('oro/translator');

interface AttributesListWithVariationsProps {
  product: Product;
  criterionCode: string;
  attributes: AttributeWithRecommendation[];
  locale: string;
  axis: string;
  evaluation: Evaluation;
}

const AttributesListWithVariations = ({product, criterionCode, attributes, locale, axis, evaluation}: AttributesListWithVariationsProps) => {

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
    isRootProductModel(product)
      ? <AttributesList product={product} criterionCode={criterionCode} attributes={getLevelAttributes(attributes, ROOT_PRODUCT_MODEL_LEVEL)} axis={axis} evaluation={evaluation}/>
      : <div className="CriterionVariantRecommendations">
        {product.meta.variant_navigation
          // @ts-ignore
          .filter((_: any, level) => level <= product.meta.level)
          .map((variant: any, level: number) => {
            return (
              <div key={`variant-attributes-list-${level}`} className="attributeList">
                <span>{level > 0 ? variant.axes[locale] : __('pim_enrich.entity.product.module.variant_navigation.common')}</span>:&thinsp;
                <AttributesList product={product} criterionCode={criterionCode} attributes={getLevelAttributes(attributes, level)} axis={axis} evaluation={evaluation}/>
              </div>
            )
        })}
      </div>
  );
};

export default AttributesListWithVariations;
