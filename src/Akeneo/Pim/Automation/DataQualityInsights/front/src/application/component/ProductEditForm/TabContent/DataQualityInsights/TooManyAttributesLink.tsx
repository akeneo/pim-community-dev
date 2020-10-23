import React, {FunctionComponent} from 'react';
import {Product} from '../../../../../domain';
import {followAttributesListRecommendation, FollowAttributesListRecommendationHandler} from '../../../../user-actions';

const translate = require('oro/translator');

interface TooManyAttributesLinkProps {
  axis: string;
  attributes: string[];
  numOfAttributes : number;
  product: Product;
  followRecommendation?: FollowAttributesListRecommendationHandler;
}

const TooManyAttributesLink: FunctionComponent<TooManyAttributesLinkProps> = ({
    axis,
    attributes,
    numOfAttributes,
    product,
    followRecommendation = followAttributesListRecommendation
}) => {
  return (
    <>
      <button
          className="AknActionButton AknActionButton--withoutBorder AknDataQualityInsightsManyAttributes"
          onClick={() => followRecommendation(product, attributes, axis)}
      >
        {translate('akeneo_data_quality_insights.product_evaluation.messages.too_many_attributes', {count: numOfAttributes})}
      </button>
    </>
  );
};

export {TooManyAttributesLink};
