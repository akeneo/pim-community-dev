import React, {FunctionComponent} from 'react';
import {Product} from '../../../../../domain';
import {followAttributesListRecommendations} from '../../../../user-actions';

const translate = require('oro/translator');

interface TooManyAttributesLinkProps {
  axis: string;
  attributes: string[];
  numOfAttributes : number;
  product: Product;
  follow?: FollowAttributesListRecommendationsHandler;
}

type FollowAttributesListRecommendationsHandler = (product: Product, attributes: string[], axis: string) => void;

const TooManyAttributesLink: FunctionComponent<TooManyAttributesLinkProps> = ({
    axis,
    attributes,
    numOfAttributes,
    product,
    follow = followAttributesListRecommendations
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
export type {FollowAttributesListRecommendationsHandler};
