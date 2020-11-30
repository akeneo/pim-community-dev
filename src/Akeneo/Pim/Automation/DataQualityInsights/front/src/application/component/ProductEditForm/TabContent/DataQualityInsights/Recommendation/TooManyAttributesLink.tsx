import React, {FunctionComponent} from 'react';
import {
  followAttributesListRecommendation,
  FollowAttributesListRecommendationHandler,
} from '../../../../../user-actions';
import {useProduct} from '../../../../../../infrastructure/hooks';

const translate = require('oro/translator');

interface TooManyAttributesLinkProps {
  axis: string;
  attributes: string[];
  numOfAttributes: number;
  followRecommendation?: FollowAttributesListRecommendationHandler;
}

const TooManyAttributesLink: FunctionComponent<TooManyAttributesLinkProps> = ({
  axis,
  attributes,
  numOfAttributes,
  followRecommendation = followAttributesListRecommendation,
}) => {
  const product = useProduct();
  return (
    <>
      <button
        className="AknActionButton AknActionButton--withoutBorder AknDataQualityInsightsManyAttributes"
        onClick={() => followRecommendation(product, attributes, axis)}
      >
        {translate('akeneo_data_quality_insights.product_evaluation.messages.too_many_attributes', {
          count: numOfAttributes,
        })}
      </button>
    </>
  );
};

export {TooManyAttributesLink};
