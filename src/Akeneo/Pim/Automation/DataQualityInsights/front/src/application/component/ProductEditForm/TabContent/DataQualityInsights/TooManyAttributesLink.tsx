import React, {FunctionComponent} from 'react';
import {Product} from '../../../../../domain';
import {followAttributesListRecommendation, FollowAttributesListRecommendationHandler} from '../../../../user-actions';

const translate = require('oro/translator');

interface TooManyAttributesLinkProps {
  axis: string;
  attributes: string[];
  numOfAttributes : number;
  product: Product;
  follow?: FollowAttributesListRecommendationHandler;
}

const TooManyAttributesLink: FunctionComponent<TooManyAttributesLinkProps> = ({
    axis,
    attributes,
    numOfAttributes,
    product,
    follow = followAttributesListRecommendation
}) => {
  return (
    <>
      <button
          className="AknActionButton AknActionButton--withoutBorder AknDataQualityInsightsManyAttributes"
          onClick={() => follow(product, attributes, axis)}
      >
        {translate('akeneo_data_quality_insights.product_evaluation.messages.too_many_attributes', {count: numOfAttributes})}
      </button>
    </>
  );
};

export {TooManyAttributesLink};
