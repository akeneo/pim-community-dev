import React, {FC} from 'react';
import {useCatalogContext, useFetchProductFamilyInformation} from '../../../../../../infrastructure/hooks';
import AttributeWithRecommendation from '../../../../../../domain/AttributeWithRecommendation.interface';
import AttributesList from '../AttributesList';
import AttributesListWithVariations from '../AttributesListWithVariations';
import {isSimpleProduct} from '../../../../../helper';
import {Evaluation, Product} from '../../../../../../domain';
import {
  FollowAttributeRecommendationHandler,
  FollowAttributesListRecommendationHandler,
} from '../../../../../user-actions';

interface RecommendationAttributesListProps {
  criterion: string;
  attributes: string[];
  product: Product;
  axis: string;
  evaluation: Evaluation;
  followAttributeRecommendation?: FollowAttributeRecommendationHandler;
  followAttributesListRecommendation?: FollowAttributesListRecommendationHandler;
}

const getAttributeLabel = (attributeCode: string, productFamilyInformation: any, locale: string) => {
  if (!productFamilyInformation.attributes) {
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

const RecommendationAttributesList: FC<RecommendationAttributesListProps> = ({
 criterion,
 attributes,
 axis,
 evaluation,
 product,
 followAttributeRecommendation,
 followAttributesListRecommendation
}) => {
  const {locale} = useCatalogContext();
  const productFamilyInformation = useFetchProductFamilyInformation();

  const attributesLabels: AttributeWithRecommendation[] = attributes.map((attributeCode: string) => {
    let label: string = attributeCode;
    if (locale && productFamilyInformation)  {
      label = getAttributeLabel(attributeCode, productFamilyInformation, locale)
    }

    return {
      code: attributeCode,
      label,
    }
  });

  const sortedAttributes = Object.values(attributesLabels).sort((attribute1: AttributeWithRecommendation, attribute2: AttributeWithRecommendation) => {
    return attribute1.label.localeCompare(attribute2.label, undefined , {sensitivity: 'base'});
  });

  return (
    <>
      {
        attributes.length === 0 ?
          <span className="NotApplicableAttribute">N/A</span> :

          isSimpleProduct(product)
            ? <AttributesList
                product={product}
                criterionCode={criterion}
                attributes={sortedAttributes}
                axis={axis}
                evaluation={evaluation}
                followAttributeRecommendation={followAttributeRecommendation}
                followAttributesListRecommendation={followAttributesListRecommendation}
              />
            : <AttributesListWithVariations
                product={product}
                criterionCode={criterion}
                attributes={sortedAttributes}
                locale={locale as string}
                evaluation={evaluation}
                axis={axis}
                followAttributeRecommendation={followAttributeRecommendation}
                followAttributesListRecommendation={followAttributesListRecommendation}
              />
      }
    </>
  )
};

export {RecommendationAttributesList};
