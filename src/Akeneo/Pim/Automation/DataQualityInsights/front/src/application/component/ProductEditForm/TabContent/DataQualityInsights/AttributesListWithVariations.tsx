import {AttributeWithRecommendation, Evaluation, Product} from '../../../../../domain';
import React, {FC} from 'react';
import AttributesList from './AttributesList';
import {isRootProductModel} from '../../../../helper';
import {ROOT_PRODUCT_MODEL_LEVEL} from '../../../../constant';
import {
  FollowAttributeRecommendationHandler,
  FollowAttributesListRecommendationHandler,
} from '../../../../user-actions';

const __ = require('oro/translator');

interface AttributesListWithVariationsProps {
  product: Product;
  criterionCode: string;
  attributes: AttributeWithRecommendation[];
  locale: string;
  axis: string;
  evaluation: Evaluation;
  followAttributeRecommendation?: FollowAttributeRecommendationHandler;
  followAttributesListRecommendation?: FollowAttributesListRecommendationHandler;
}

const AttributesListWithVariations: FC<AttributesListWithVariationsProps> = ({product, criterionCode, attributes, locale, axis, evaluation, followAttributeRecommendation, followAttributesListRecommendation}) => {

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
      ? <AttributesList
            product={product}
            criterionCode={criterionCode}
            attributes={getLevelAttributes(attributes, ROOT_PRODUCT_MODEL_LEVEL)}
            axis={axis}
            evaluation={evaluation}
            followAttributeRecommendation={followAttributeRecommendation}
            followAttributesListRecommendation={followAttributesListRecommendation}
        />
      : <div className="CriterionVariantRecommendations">
        {product.meta.variant_navigation
          // @ts-ignore
          .filter((_: any, level) => level <= product.meta.level)
          .map((variant: any, level: number) => {
            return (
              <div key={`variant-attributes-list-${level}`} className="attributeList" data-testid={`attributes-level-${level}`}>
                <span>{level > 0 ? variant.axes[locale] : __('pim_enrich.entity.product.module.variant_navigation.common')}</span>:&thinsp;
                <AttributesList
                    product={product}
                    criterionCode={criterionCode}
                    attributes={getLevelAttributes(attributes, level)}
                    axis={axis}
                    evaluation={evaluation}
                    followAttributeRecommendation={followAttributeRecommendation}
                    followAttributesListRecommendation={followAttributesListRecommendation}
                />
              </div>
            )
        })}
      </div>
  );
};

export default AttributesListWithVariations;
