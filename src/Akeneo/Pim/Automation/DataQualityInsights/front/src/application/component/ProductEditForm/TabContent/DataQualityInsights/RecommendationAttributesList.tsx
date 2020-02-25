import React, {FunctionComponent} from 'react';
import {useCatalogContext, useFetchProductFamilyInformation} from "../../../../../infrastructure/hooks";
import Attribute from "./Attribute";

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

  let attributesLabels: any[] = [];
  if (locale && productFamilyInformation) {
    attributesLabels = attributes.map((attributeCode: string) => {
      return {
        code: attributeCode,
        label: getAttributeLabel(attributeCode, productFamilyInformation, locale),
      }
    });
  }

  const sortedAttributes = Object.values(attributesLabels).sort((attribute1: any, attribute2: any) => {
    return attribute1.label.localeCompare(attribute2.label, undefined , {sensitivity: 'base'});
  });

  return (
    <>
      {
        attributes.length === 0 ?
          <span className="NotApplicableAttribute">N/A</span> :
          <>
            {sortedAttributes.map((attribute: any, index: number) => {
              const separator = (
                <>
                  {(index < (attributes.length - 1)) && <>,&thinsp;</>}
                  {(index === (attributes.length - 1)) && '.'}
                </>
              );

              return (
                <Attribute key={`attribute-${criterion}-${index}`} code={attribute.code} label={attribute.label} separator={separator}/>
              );
            })}
          </>
      }
    </>
  )
};

export default RecommendationAttributesList;
