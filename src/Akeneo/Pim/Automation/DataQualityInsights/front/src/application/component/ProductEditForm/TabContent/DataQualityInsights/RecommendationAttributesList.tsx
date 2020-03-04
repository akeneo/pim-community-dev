import React, {FunctionComponent} from 'react';
import {useCatalogContext, useFetchProductFamilyInformation, useProduct} from '../../../../../infrastructure/hooks';
import AttributeWithRecommendation from '../../../../../domain/AttributeWithRecommendation.interface';
import AttributesList from './AttributesList';
import VariantAttributesList from './VariantAttributesList';
import {isSimpleProduct} from "../../../../helper/ProductEditForm/Product";

interface RecommendationAttributesListProps {
  criterion: string;
  attributes: string[];
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

const RecommendationAttributesList: FunctionComponent<RecommendationAttributesListProps> = ({criterion, attributes}) => {
  const {locale} = useCatalogContext();
  const productFamilyInformation = useFetchProductFamilyInformation();
  const product = useProduct();

  let attributesLabels: AttributeWithRecommendation[] = [];
  if (locale && productFamilyInformation) {
    attributesLabels = attributes.map((attributeCode: string) => {
      return {
        code: attributeCode,
        label: getAttributeLabel(attributeCode, productFamilyInformation, locale),
      }
    });
  }

  const sortedAttributes = Object.values(attributesLabels).sort((attribute1: AttributeWithRecommendation, attribute2: AttributeWithRecommendation) => {
    return attribute1.label.localeCompare(attribute2.label, undefined , {sensitivity: 'base'});
  });

  return (
    <>
      {
        attributes.length === 0 ?
          <span className="NotApplicableAttribute">N/A</span> :
          isSimpleProduct(product) ?
            <AttributesList product={product} criterionCode={criterion} attributes={sortedAttributes}/> :
            // @ts-ignore
            <VariantAttributesList product={product} criterionCode={criterion} attributes={sortedAttributes} locale={locale}/>
      }
    </>
  )
};

export default RecommendationAttributesList;
