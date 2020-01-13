import React, {Fragment, FunctionComponent} from 'react';
import Attribute from "./Attribute";
import {useCatalogContext, useFetchProductFamilyInformation} from "../../../../../infrastructure/hooks";

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

  return (
    <>
      {locale && productFamilyInformation && attributes.map((attributeCode: string, index) => {
        return (
          <Fragment key={`attribute-${criterion}-${index}`}>
            <Attribute code={attributeCode}>
              {getAttributeLabel(attributeCode, productFamilyInformation, locale)}
            </Attribute>
            {(index < (attributes.length -1)) && ', '}
            {(index === (attributes.length -1)) && '.'}
          </Fragment>
        );
      })}
    </>
  );
};

export default RecommendationAttributesList;
