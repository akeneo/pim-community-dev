import React, {FunctionComponent} from 'react';
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

const compareAttributeCode = (code1: string, code2: string) => {
  return code1.localeCompare(code2, undefined , {sensitivity: 'base'});
}

const RecommendationAttributesList: FunctionComponent<RecommendationAttributesListProps> = ({criterion, attributes}) => {
  const {locale} = useCatalogContext();
  const productFamilyInformation = useFetchProductFamilyInformation();

    return (
      <>
        { attributes.length == 0 ? (
            <span className="NonApplicableAttribute">N/A</span>
          ) : (
          <>
            {locale && productFamilyInformation && attributes.sort(compareAttributeCode).map((attributeCode: string, index) => {
              return (
                <Attribute key={`attribute-${criterion}-${index}`} code={attributeCode}>
                  {getAttributeLabel(attributeCode, productFamilyInformation, locale)}
                  {(index < (attributes.length - 1)) && <>,&thinsp;</>}
                  {(index === (attributes.length - 1)) && '.'}
                </Attribute>
              );
            })}
          </>
          )
        }
      </>
    )
};

export default RecommendationAttributesList;
