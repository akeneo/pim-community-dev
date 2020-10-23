import React, {FunctionComponent, ReactElement} from 'react';
import {Product} from '../../../../../domain';
import {followAttributeRecommendation, FollowAttributeRecommendationHandler} from '../../../../user-actions';

interface AttributeProps {
  attributeCode: string;
  label: string;
  separator: ReactElement | null;
  product: Product;
  followRecommendation?: FollowAttributeRecommendationHandler;
}


const Attribute: FunctionComponent<AttributeProps> = ({
  attributeCode,
  label,
  separator,
  product,
  followRecommendation = followAttributeRecommendation,
}) => {
  const content = (
    <>
      <span data-testid={'dqiAttributeWithRecommendation'}>{label}</span>
      {separator}
    </>
  );

  return (
    <button
      className="AknActionButton AknActionButton--withoutBorder AknDataQualityInsightsAttribute AknDataQualityInsightsAttribute--link"
      onClick={() => followRecommendation(attributeCode, product)}
    >
      {content}
    </button>
  );
};

export default Attribute;
export type {FollowAttributeRecommendationHandler};
