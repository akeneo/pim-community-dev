import AttributeWithRecommendation from '../../../../../domain/AttributeWithRecommendation.interface';
import Attribute from './Attribute';
import React from 'react';
import {Product} from '../../../../../domain';

const __ = require('oro/translator');

interface AttributesListProps {
  product: Product;
  criterionCode: string;
  attributes: AttributeWithRecommendation[];
}

const AttributesList = ({product, criterionCode, attributes}: AttributesListProps) => {
  if (attributes.length === 0) {
    return (
      <span className="CriterionSuccessMessage">
        {__(`akeneo_data_quality_insights.product_evaluation.messages.success.criterion`)}
      </span>
    )
  }

  return (
    <>
      {attributes.map((attribute: AttributeWithRecommendation, index: number) => {
        const separator = (
          <>
            {(index < (attributes.length - 1)) && <>,&thinsp;</>}
            {(index === (attributes.length - 1)) && '.'}
          </>
        );

        return (
          <Attribute
            key={`attribute-${criterionCode}-${index}`}
            attributeCode={attribute.code}
            label={attribute.label}
            separator={separator}
            product={product}
          />
        );
      })}
    </>
  );
};

export default AttributesList;
