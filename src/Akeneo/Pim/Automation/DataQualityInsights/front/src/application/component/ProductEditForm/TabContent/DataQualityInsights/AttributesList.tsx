import React, {FC} from 'react';
import AttributeWithRecommendation from '../../../../../domain/AttributeWithRecommendation.interface';
import Attribute, {FollowAttributeRecommendationHandler} from './Attribute';
import {CriterionEvaluationResult, Evaluation, Product} from '../../../../../domain';
import {TooManyAttributesLink} from './TooManyAttributesLink';
import {uniq as _uniq} from 'lodash';
import {FollowAttributesListRecommendationHandler} from '../../../../user-actions';

const __ = require('oro/translator');

const MAX_ATTRIBUTES_DISPLAYED = 15;

interface AttributesListProps {
  product: Product;
  criterionCode: string;
  attributes: AttributeWithRecommendation[];
  axis: string;
  evaluation: Evaluation;
  followAttributeRecommendation?: FollowAttributeRecommendationHandler;
  followAttributesListRecommendation?: FollowAttributesListRecommendationHandler;
}

const getAxisAttributesWithRecommendations = (criteria: CriterionEvaluationResult[]): string[] => {
  let attributes: string[] = [];

  criteria.map(criterion => {
    attributes = [...criterion.improvable_attributes, ...attributes];
  });

  return _uniq(attributes);
};

const AttributesList: FC<AttributesListProps> = ({product, criterionCode, attributes, axis, evaluation, followAttributeRecommendation, followAttributesListRecommendation}) => {
  const criteria = evaluation.criteria || [];
  const allAttributes = getAxisAttributesWithRecommendations(criteria);

  if (attributes.length === 0) {
    return (
      <span className="CriterionSuccessMessage">
        {__(`akeneo_data_quality_insights.product_evaluation.messages.success.criterion`)}
      </span>
    );
  }

  if (attributes.length <= MAX_ATTRIBUTES_DISPLAYED) {
    return (
      <>
        {attributes.map((attribute: AttributeWithRecommendation, index: number) => {
          const separator = (
            <>
              {index < attributes.length - 1 && <>,&thinsp;</>}
              {index === attributes.length - 1 && '.'}
            </>
          );

          return (
            <Attribute
              key={`attribute-${criterionCode}-${index}`}
              attributeCode={attribute.code}
              label={attribute.label}
              separator={separator}
              product={product}
              followRecommendation={followAttributeRecommendation}
            />
          );
        })}
      </>
    );
  } else {
    return (
      <TooManyAttributesLink
        axis={axis}
        attributes={allAttributes}
        numOfAttributes={attributes.length}
        product={product}
        followRecommendation={followAttributesListRecommendation}
      />
    );
  }
};

export default AttributesList;
